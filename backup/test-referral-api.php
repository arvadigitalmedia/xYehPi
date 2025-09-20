<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Referral API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Test Referral API & Cookie System</h1>
        
        <!-- Test API -->
        <div class="bg-gray-800 p-6 rounded-lg mb-6">
            <h2 class="text-lg font-semibold mb-4">Test API Endpoint</h2>
            <div class="flex gap-3 mb-4">
                <input type="text" id="testCode" placeholder="Masukkan kode referral" 
                       class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white">
                <button onclick="testAPI()" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded">
                    Test API
                </button>
            </div>
            <div id="apiResult" class="bg-gray-700 p-4 rounded text-sm font-mono"></div>
        </div>
        
        <!-- Test Cookie -->
        <div class="bg-gray-800 p-6 rounded-lg mb-6">
            <h2 class="text-lg font-semibold mb-4">Test Cookie System</h2>
            <div class="flex gap-3 mb-4">
                <input type="text" id="cookieCode" placeholder="Kode referral untuk cookie" 
                       class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded text-white">
                <button onclick="setCookie()" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded">
                    Set Cookie
                </button>
                <button onclick="getCookie()" 
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 rounded">
                    Get Cookie
                </button>
                <button onclick="clearCookie()" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded">
                    Clear Cookie
                </button>
            </div>
            <div id="cookieResult" class="bg-gray-700 p-4 rounded text-sm font-mono"></div>
        </div>
        
        <!-- Test Links -->
        <div class="bg-gray-800 p-6 rounded-lg">
            <h2 class="text-lg font-semibold mb-4">Test Links</h2>
            <div class="space-y-2">
                <a href="/test-bisnisemasperak/register?ref=HKJPX53B" 
                   class="block px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-center">
                    Test dengan HKJPX53B
                </a>
                <a href="/test-bisnisemasperak/register" 
                   class="block px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded text-center">
                    Test tanpa referral (cek cookie)
                </a>
            </div>
        </div>
    </div>

    <script>
        async function testAPI() {
            const code = document.getElementById('testCode').value;
            const resultDiv = document.getElementById('apiResult');
            
            if (!code) {
                resultDiv.textContent = 'Masukkan kode referral terlebih dahulu';
                return;
            }
            
            try {
                resultDiv.textContent = 'Loading...';
                
                const response = await fetch('/test-bisnisemasperak/api/check-referral.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ referral_code: code })
                });
                
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                
            } catch (error) {
                resultDiv.textContent = 'Error: ' + error.message;
            }
        }
        
        function setCookie() {
            const code = document.getElementById('cookieCode').value;
            const resultDiv = document.getElementById('cookieResult');
            
            if (!code) {
                resultDiv.textContent = 'Masukkan kode referral terlebih dahulu';
                return;
            }
            
            const cookieData = {
                code: code,
                timestamp: Date.now(),
                ip: 'test',
                source: 'manual'
            };
            
            const cookieValue = btoa(JSON.stringify(cookieData));
            const expireDate = new Date();
            expireDate.setTime(expireDate.getTime() + (30 * 24 * 60 * 60 * 1000));
            
            document.cookie = `epic_referral=${cookieValue}; expires=${expireDate.toUTCString()}; path=/; SameSite=Lax`;
            document.cookie = `epic_ref_code=${code}; expires=${expireDate.toUTCString()}; path=/; SameSite=Lax`;
            
            resultDiv.textContent = 'Cookie set successfully:\n' + JSON.stringify(cookieData, null, 2);
        }
        
        function getCookie() {
            const resultDiv = document.getElementById('cookieResult');
            const cookies = document.cookie.split(';').reduce((acc, cookie) => {
                const [key, value] = cookie.trim().split('=');
                acc[key] = value;
                return acc;
            }, {});
            
            const result = {
                epic_referral: cookies.epic_referral ? JSON.parse(atob(cookies.epic_referral)) : null,
                epic_ref_code: cookies.epic_ref_code || null,
                epic_ref_name: cookies.epic_ref_name || null,
                all_cookies: cookies
            };
            
            resultDiv.textContent = JSON.stringify(result, null, 2);
        }
        
        function clearCookie() {
            const resultDiv = document.getElementById('cookieResult');
            
            document.cookie = 'epic_referral=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'epic_ref_code=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'epic_ref_name=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            
            resultDiv.textContent = 'Cookies cleared successfully';
        }
        
        // Auto-load current cookies on page load
        window.addEventListener('load', getCookie);
    </script>
</body>
</html>