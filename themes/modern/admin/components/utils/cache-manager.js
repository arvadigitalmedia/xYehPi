/**
 * EPIC Hub Admin Cache Manager
 * Handles caching for components, data, and assets with intelligent invalidation
 */

export class CacheManager {
    constructor() {
        this.memoryCache = new Map();
        this.storageCache = new Map();
        this.cacheConfig = {
            maxMemorySize: 50 * 1024 * 1024, // 50MB
            maxStorageSize: 100 * 1024 * 1024, // 100MB
            defaultTTL: 30 * 60 * 1000, // 30 minutes
            cleanupInterval: 5 * 60 * 1000, // 5 minutes
        };
        this.currentMemorySize = 0;
        this.currentStorageSize = 0;
        this.cleanupTimer = null;
        
        this.init();
    }

    /**
     * Initialize cache manager
     */
    init() {
        this.loadStorageCache();
        this.startCleanupTimer();
        this.setupEventListeners();
        
        console.log('Cache Manager initialized');
    }

    /**
     * Setup event listeners for cache management
     */
    setupEventListeners() {
        // Clear cache on storage quota exceeded
        window.addEventListener('storage', (e) => {
            if (e.key === null) {
                // Storage was cleared
                this.clearStorageCache();
            }
        });

        // Clear cache before page unload if needed
        window.addEventListener('beforeunload', () => {
            this.saveStorageCache();
        });

        // Handle visibility change to manage cache
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.saveStorageCache();
            } else {
                this.cleanupExpiredEntries();
            }
        });
    }

    /**
     * Get cached data with fallback to fetch function
     */
    async getCachedData(key, fetchFunction, options = {}) {
        const config = {
            ttl: options.ttl || this.cacheConfig.defaultTTL,
            useStorage: options.useStorage || false,
            forceRefresh: options.forceRefresh || false,
            ...options
        };

        // Check if force refresh is requested
        if (config.forceRefresh) {
            return await this.fetchAndCache(key, fetchFunction, config);
        }

        // Try memory cache first
        const memoryData = this.getFromMemoryCache(key);
        if (memoryData && !this.isExpired(memoryData)) {
            return memoryData.data;
        }

        // Try storage cache if enabled
        if (config.useStorage) {
            const storageData = this.getFromStorageCache(key);
            if (storageData && !this.isExpired(storageData)) {
                // Move to memory cache for faster access
                this.setMemoryCache(key, storageData.data, config.ttl);
                return storageData.data;
            }
        }

        // Fetch new data
        return await this.fetchAndCache(key, fetchFunction, config);
    }

    /**
     * Fetch data and cache it
     */
    async fetchAndCache(key, fetchFunction, config) {
        try {
            const startTime = performance.now();
            const data = await fetchFunction();
            const fetchTime = performance.now() - startTime;

            // Cache the data
            this.setMemoryCache(key, data, config.ttl, { fetchTime });
            
            if (config.useStorage) {
                this.setStorageCache(key, data, config.ttl, { fetchTime });
            }

            console.log(`Cached data for key: ${key} (fetch time: ${fetchTime.toFixed(2)}ms)`);
            return data;
        } catch (error) {
            console.error(`Failed to fetch and cache data for key: ${key}`, error);
            
            // Try to return stale data if available
            const staleData = this.getStaleData(key);
            if (staleData) {
                console.warn(`Returning stale data for key: ${key}`);
                return staleData;
            }
            
            throw error;
        }
    }

    /**
     * Set data in memory cache
     */
    setMemoryCache(key, data, ttl = this.cacheConfig.defaultTTL, metadata = {}) {
        const entry = {
            data,
            timestamp: Date.now(),
            ttl,
            size: this.calculateSize(data),
            metadata
        };

        // Check if we need to free up space
        if (this.currentMemorySize + entry.size > this.cacheConfig.maxMemorySize) {
            this.evictMemoryCache(entry.size);
        }

        // Remove existing entry if it exists
        if (this.memoryCache.has(key)) {
            const oldEntry = this.memoryCache.get(key);
            this.currentMemorySize -= oldEntry.size;
        }

        this.memoryCache.set(key, entry);
        this.currentMemorySize += entry.size;
    }

    /**
     * Get data from memory cache
     */
    getFromMemoryCache(key) {
        return this.memoryCache.get(key);
    }

    /**
     * Set data in storage cache
     */
    setStorageCache(key, data, ttl = this.cacheConfig.defaultTTL, metadata = {}) {
        try {
            const entry = {
                data,
                timestamp: Date.now(),
                ttl,
                metadata
            };

            const serialized = JSON.stringify(entry);
            const size = new Blob([serialized]).size;

            // Check storage quota
            if (this.currentStorageSize + size > this.cacheConfig.maxStorageSize) {
                this.evictStorageCache(size);
            }

            localStorage.setItem(`epic-cache-${key}`, serialized);
            this.storageCache.set(key, { ...entry, size });
            this.currentStorageSize += size;
        } catch (error) {
            console.warn(`Failed to set storage cache for key: ${key}`, error);
            
            // Try to free up space and retry
            if (error.name === 'QuotaExceededError') {
                this.evictStorageCache(this.cacheConfig.maxStorageSize * 0.3);
                try {
                    localStorage.setItem(`epic-cache-${key}`, JSON.stringify(entry));
                } catch (retryError) {
                    console.error('Failed to cache after cleanup:', retryError);
                }
            }
        }
    }

    /**
     * Get data from storage cache
     */
    getFromStorageCache(key) {
        try {
            const stored = localStorage.getItem(`epic-cache-${key}`);
            if (stored) {
                return JSON.parse(stored);
            }
        } catch (error) {
            console.warn(`Failed to get storage cache for key: ${key}`, error);
            localStorage.removeItem(`epic-cache-${key}`);
        }
        return null;
    }

    /**
     * Check if cache entry is expired
     */
    isExpired(entry) {
        return Date.now() - entry.timestamp > entry.ttl;
    }

    /**
     * Get stale data (expired but still available)
     */
    getStaleData(key) {
        const memoryData = this.getFromMemoryCache(key);
        if (memoryData) {
            return memoryData.data;
        }

        const storageData = this.getFromStorageCache(key);
        if (storageData) {
            return storageData.data;
        }

        return null;
    }

    /**
     * Evict entries from memory cache to free up space
     */
    evictMemoryCache(requiredSpace) {
        const entries = Array.from(this.memoryCache.entries())
            .map(([key, entry]) => ({ key, ...entry }))
            .sort((a, b) => {
                // Sort by access time and size (LRU + size-based)
                const aScore = (Date.now() - a.timestamp) / a.ttl + (a.size / 1024);
                const bScore = (Date.now() - b.timestamp) / b.ttl + (b.size / 1024);
                return bScore - aScore;
            });

        let freedSpace = 0;
        for (const entry of entries) {
            this.memoryCache.delete(entry.key);
            this.currentMemorySize -= entry.size;
            freedSpace += entry.size;
            
            if (freedSpace >= requiredSpace) {
                break;
            }
        }

        console.log(`Evicted ${freedSpace} bytes from memory cache`);
    }

    /**
     * Evict entries from storage cache to free up space
     */
    evictStorageCache(requiredSpace) {
        const entries = Array.from(this.storageCache.entries())
            .map(([key, entry]) => ({ key, ...entry }))
            .sort((a, b) => {
                const aScore = (Date.now() - a.timestamp) / a.ttl;
                const bScore = (Date.now() - b.timestamp) / b.ttl;
                return bScore - aScore;
            });

        let freedSpace = 0;
        for (const entry of entries) {
            localStorage.removeItem(`epic-cache-${entry.key}`);
            this.storageCache.delete(entry.key);
            this.currentStorageSize -= entry.size;
            freedSpace += entry.size;
            
            if (freedSpace >= requiredSpace) {
                break;
            }
        }

        console.log(`Evicted ${freedSpace} bytes from storage cache`);
    }

    /**
     * Calculate approximate size of data
     */
    calculateSize(data) {
        try {
            return new Blob([JSON.stringify(data)]).size;
        } catch (error) {
            // Fallback estimation
            return JSON.stringify(data).length * 2; // Rough estimate
        }
    }

    /**
     * Load storage cache into memory for tracking
     */
    loadStorageCache() {
        try {
            let totalSize = 0;
            
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith('epic-cache-')) {
                    const cacheKey = key.replace('epic-cache-', '');
                    const data = this.getFromStorageCache(cacheKey);
                    
                    if (data) {
                        const size = this.calculateSize(data);
                        this.storageCache.set(cacheKey, { ...data, size });
                        totalSize += size;
                    }
                }
            }
            
            this.currentStorageSize = totalSize;
            console.log(`Loaded storage cache: ${totalSize} bytes`);
        } catch (error) {
            console.warn('Failed to load storage cache:', error);
        }
    }

    /**
     * Save current cache state to storage
     */
    saveStorageCache() {
        // This is handled automatically by setStorageCache
        // But we can add any additional persistence logic here
    }

    /**
     * Start cleanup timer for expired entries
     */
    startCleanupTimer() {
        this.cleanupTimer = setInterval(() => {
            this.cleanupExpiredEntries();
        }, this.cacheConfig.cleanupInterval);
    }

    /**
     * Cleanup expired entries from both caches
     */
    cleanupExpiredEntries() {
        let memoryCleanedSize = 0;
        let storageCleanedSize = 0;
        let memoryCleanedCount = 0;
        let storageCleanedCount = 0;

        // Cleanup memory cache
        for (const [key, entry] of this.memoryCache.entries()) {
            if (this.isExpired(entry)) {
                this.memoryCache.delete(key);
                this.currentMemorySize -= entry.size;
                memoryCleanedSize += entry.size;
                memoryCleanedCount++;
            }
        }

        // Cleanup storage cache
        for (const [key, entry] of this.storageCache.entries()) {
            if (this.isExpired(entry)) {
                localStorage.removeItem(`epic-cache-${key}`);
                this.storageCache.delete(key);
                this.currentStorageSize -= entry.size;
                storageCleanedSize += entry.size;
                storageCleanedCount++;
            }
        }

        if (memoryCleanedCount > 0 || storageCleanedCount > 0) {
            console.log(`Cache cleanup: Memory (${memoryCleanedCount} entries, ${memoryCleanedSize} bytes), Storage (${storageCleanedCount} entries, ${storageCleanedSize} bytes)`);
        }
    }

    /**
     * Clear specific cache entry
     */
    clear(key) {
        // Clear from memory cache
        if (this.memoryCache.has(key)) {
            const entry = this.memoryCache.get(key);
            this.currentMemorySize -= entry.size;
            this.memoryCache.delete(key);
        }

        // Clear from storage cache
        if (this.storageCache.has(key)) {
            const entry = this.storageCache.get(key);
            this.currentStorageSize -= entry.size;
            this.storageCache.delete(key);
            localStorage.removeItem(`epic-cache-${key}`);
        }
    }

    /**
     * Clear all memory cache
     */
    clearMemoryCache() {
        this.memoryCache.clear();
        this.currentMemorySize = 0;
        console.log('Memory cache cleared');
    }

    /**
     * Clear all storage cache
     */
    clearStorageCache() {
        // Remove all epic-cache entries from localStorage
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('epic-cache-')) {
                keysToRemove.push(key);
            }
        }

        keysToRemove.forEach(key => localStorage.removeItem(key));
        this.storageCache.clear();
        this.currentStorageSize = 0;
        console.log('Storage cache cleared');
    }

    /**
     * Clear all caches
     */
    clearAll() {
        this.clearMemoryCache();
        this.clearStorageCache();
    }

    /**
     * Get cache statistics
     */
    getStats() {
        return {
            memory: {
                entries: this.memoryCache.size,
                size: this.currentMemorySize,
                maxSize: this.cacheConfig.maxMemorySize,
                utilization: (this.currentMemorySize / this.cacheConfig.maxMemorySize * 100).toFixed(2) + '%'
            },
            storage: {
                entries: this.storageCache.size,
                size: this.currentStorageSize,
                maxSize: this.cacheConfig.maxStorageSize,
                utilization: (this.currentStorageSize / this.cacheConfig.maxStorageSize * 100).toFixed(2) + '%'
            },
            config: this.cacheConfig
        };
    }

    /**
     * Update cache configuration
     */
    updateConfig(newConfig) {
        this.cacheConfig = { ...this.cacheConfig, ...newConfig };
        
        // Restart cleanup timer if interval changed
        if (newConfig.cleanupInterval) {
            clearInterval(this.cleanupTimer);
            this.startCleanupTimer();
        }
    }

    /**
     * Preload data into cache
     */
    async preload(entries) {
        const promises = entries.map(async ({ key, fetchFunction, options }) => {
            try {
                await this.getCachedData(key, fetchFunction, options);
                console.log(`Preloaded cache entry: ${key}`);
            } catch (error) {
                console.warn(`Failed to preload cache entry: ${key}`, error);
            }
        });

        await Promise.allSettled(promises);
    }

    /**
     * Destroy cache manager
     */
    destroy() {
        if (this.cleanupTimer) {
            clearInterval(this.cleanupTimer);
        }
        
        this.saveStorageCache();
        this.clearMemoryCache();
        
        console.log('Cache Manager destroyed');
    }
}

// Export for use in other modules
export default CacheManager;