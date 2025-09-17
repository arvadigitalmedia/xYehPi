# EPIC Hub Member Area - Design Standards

## Overview
Standar desain untuk memastikan konsistensi UI/UX di seluruh member area dengan tema Premium Dark Gold yang selaras dengan admin panel.

## Design Tokens

### Color Palette

#### Gold Palette (Primary)
```css
--gold-500: #CFA84E;  /* Primary gold */
--gold-400: #DDB966;  /* Light gold */
--gold-300: #E6CD8B;  /* Lighter gold */
--gold-200: #F0D9A8;  /* Very light gold */
--gold-100: #F8EDD0;  /* Pale gold */
```

#### Ink/Dark Palette (Background & Text)
```css
--ink-900: #0B0B0F;  /* Darkest background */
--ink-800: #141419;  /* Dark background */
--ink-700: #1D1D25;  /* Border color */
--ink-600: #262732;  /* Input borders */
--ink-500: #3A3B47;  /* Disabled elements */
--ink-400: #52535F;  /* Placeholder text */
--ink-300: #6B6C78;  /* Secondary text */
--ink-200: #9B9CA8;  /* Body text */
--ink-100: #D1D2D9;  /* Primary text */
```

#### Surface Layers
```css
--surface-1: #0F0F14;  /* Base surface */
--surface-2: #15161C;  /* Card background */
--surface-3: #1C1D24;  /* Elevated surface */
--surface-4: #23242C;  /* Highest elevation */
```

#### Status Colors
```css
--success: #10B981;   /* Success states */
--warning: #F59E0B;   /* Warning states */
--danger: #EF4444;    /* Error states */
--info: #3B82F6;      /* Info states */
```

### Typography

#### Font Family
```css
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
```

#### Font Sizes
```css
--font-size-xs: 0.75rem;    /* 12px - Small text */
--font-size-sm: 0.875rem;   /* 14px - Body text */
--font-size-base: 1rem;     /* 16px - Default */
--font-size-lg: 1.25rem;    /* 20px - Headings */
--font-size-xl: 1.75rem;    /* 28px - Large headings */
--font-size-2xl: 2rem;      /* 32px - Page titles */
--font-size-3xl: 2.5rem;    /* 40px - Hero text */
```

#### Font Weights
```css
--font-weight-normal: 400;    /* Regular text */
--font-weight-medium: 500;    /* Medium emphasis */
--font-weight-semibold: 600;  /* Strong emphasis */
--font-weight-bold: 700;      /* Bold text */
```

### Spacing System

```css
--spacing-1: 0.25rem;   /* 4px */
--spacing-2: 0.5rem;    /* 8px */
--spacing-3: 0.75rem;   /* 12px */
--spacing-4: 1rem;      /* 16px */
--spacing-5: 1.25rem;   /* 20px */
--spacing-6: 1.5rem;    /* 24px */
--spacing-8: 2rem;      /* 32px */
--spacing-10: 2.5rem;   /* 40px */
--spacing-12: 3rem;     /* 48px */
--spacing-16: 4rem;     /* 64px */
```

### Border Radius

```css
--radius-sm: 0.375rem;   /* 6px - Small elements */
--radius-md: 0.5rem;     /* 8px - Inputs */
--radius-lg: 0.75rem;    /* 12px - Buttons */
--radius-xl: 1rem;       /* 16px - Cards */
--radius-2xl: 1.125rem;  /* 18px - Large cards */
--radius-3xl: 1.25rem;   /* 20px - Hero elements */
--radius-full: 9999px;   /* Full rounded */
```

### Shadows

```css
--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
--shadow-gold: 0 4px 14px 0 rgba(207, 168, 78, 0.15);
--shadow-gold-lg: 0 10px 25px 0 rgba(207, 168, 78, 0.2);
```

## Component Standards

### Buttons

#### Primary Button
```css
.btn-primary {
  background: var(--gradient-gold);
  color: var(--ink-900);
  border-color: var(--gold-400);
  font-weight: var(--font-weight-semibold);
}
```

#### Secondary Button
```css
.btn-secondary {
  background: var(--surface-2);
  color: var(--ink-100);
  border-color: var(--ink-600);
}
```

#### Button Sizes
- **Small**: `padding: var(--spacing-2) var(--spacing-4);`
- **Default**: `padding: var(--spacing-3) var(--spacing-6);`
- **Large**: `padding: var(--spacing-4) var(--spacing-8);`

### Cards

#### Standard Card
```css
.card, .epic-card {
  background: var(--surface-2);
  border: 1px solid var(--ink-700);
  border-radius: var(--radius-2xl);
  box-shadow: var(--shadow-md);
}
```

#### Card Hover State
```css
.card:hover {
  box-shadow: var(--shadow-xl);
  transform: translateY(-2px);
  border-color: var(--gold-400);
}
```

### Forms

#### Form Input
```css
.form-input {
  background: var(--surface-3);
  border: 1px solid var(--ink-600);
  border-radius: var(--radius-lg);
  color: var(--ink-100);
  padding: var(--spacing-3) var(--spacing-4);
}
```

#### Focus State
```css
.form-input:focus {
  border-color: var(--gold-400);
  box-shadow: 0 0 0 3px rgba(207, 168, 78, 0.1);
  background: var(--surface-2);
}
```

### Navigation

#### Active Navigation Item
```css
.nav-item.active .nav-link {
  background: linear-gradient(
    45deg,
    #ffd700 0%,
    #ffed4e 20%,
    #fff9c4 40%,
    #ffed4e 60%,
    #ffd700 80%,
    #b8860b 100%
  );
  color: #1a1a1a;
  font-weight: var(--font-weight-bold);
}
```

### Statistics Cards

#### Stat Card Structure
```css
.stat-card {
  background: var(--surface-2);
  border: 1px solid var(--ink-700);
  border-radius: var(--radius-2xl);
  padding: var(--spacing-6);
  position: relative;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--gradient-gold);
}
```

## Layout Guidelines

### Page Structure
```html
<div class="member-container">
  <aside class="member-sidebar"><!-- Sidebar --></aside>
  <main class="member-main">
    <header class="member-header"><!-- Header --></header>
    <div class="member-content">
      <!-- Page content -->
    </div>
    <footer class="member-footer"><!-- Footer --></footer>
  </main>
</div>
```

### Content Layout
- **Max Width**: `var(--content-max-width)` (1440px)
- **Padding**: `var(--spacing-8)` (32px) on desktop
- **Gap**: `var(--spacing-6)` (24px) between sections

### Grid System
```css
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: var(--spacing-6);
}
```

## Responsive Breakpoints

### Desktop First Approach
```css
/* Large Desktop: 1024px+ (default) */

/* Tablet: 1024px and below */
@media (max-width: 1024px) {
  .member-main {
    margin-left: var(--sidebar-collapsed-width);
  }
}

/* Mobile: 768px and below */
@media (max-width: 768px) {
  .member-main {
    margin-left: 0;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

/* Small Mobile: 480px and below */
@media (max-width: 480px) {
  .member-content {
    padding: var(--spacing-3);
  }
}
```

## Animation Standards

### Transitions
```css
--transition-fast: 150ms ease-in-out;    /* Quick interactions */
--transition-normal: 200ms ease-in-out;  /* Standard transitions */
--transition-slow: 300ms ease-in-out;    /* Complex animations */
```

### Hover Effects
- **Cards**: `transform: translateY(-2px);`
- **Buttons**: `transform: translateY(-1px);`
- **Navigation**: `transform: translateX(2px);`

### Loading States
```css
.animate-shimmer {
  background: linear-gradient(
    90deg,
    var(--surface-2) 0px,
    var(--surface-3) 40px,
    var(--surface-2) 80px
  );
  animation: shimmer 1.5s infinite;
}
```

## Accessibility Standards

### Focus States
```css
*:focus {
  outline: 2px solid var(--gold-400);
  outline-offset: 2px;
}
```

### Color Contrast
- **Primary Text**: var(--ink-100) on var(--ink-900) - WCAG AA compliant
- **Secondary Text**: var(--ink-200) on var(--ink-900) - WCAG AA compliant
- **Gold Elements**: Ensure sufficient contrast for readability

### Interactive Elements
- **Minimum Touch Target**: 44px × 44px
- **Focus Indicators**: Always visible and high contrast
- **Screen Reader**: Use semantic HTML and ARIA labels

## Implementation Checklist

### New Page/Component
- [ ] Use design tokens from CSS variables
- [ ] Follow component structure standards
- [ ] Implement responsive design
- [ ] Add hover and focus states
- [ ] Test accessibility compliance
- [ ] Ensure consistent spacing
- [ ] Use appropriate typography scale
- [ ] Include loading and empty states

### Code Quality
- [ ] Use semantic HTML
- [ ] Follow BEM naming convention
- [ ] Optimize for performance
- [ ] Test cross-browser compatibility
- [ ] Validate HTML and CSS
- [ ] Document any custom components

## File Structure

```
themes/modern/member/
├── member-redesign.css          # Main stylesheet
├── components-redesign.css      # Component styles
├── design-standards.md          # This file
├── layout.php                   # Main layout
├── components/
│   ├── sidebar.php             # Sidebar component
│   ├── header.php              # Header component
│   └── footer.php              # Footer component
└── pages/
    ├── home.php                # Dashboard home
    ├── profile.php             # Profile management
    ├── products.php            # Product access
    └── orders.php              # Order history
```

## Maintenance

### Regular Updates
- Review design tokens quarterly
- Update components based on user feedback
- Maintain consistency with admin panel
- Test new browser features and compatibility

### Version Control
- Document all design changes
- Maintain backward compatibility when possible
- Use semantic versioning for major updates
- Keep design system documentation updated

---

**Version**: 3.0.0  
**Last Updated**: 2024  
**Maintained by**: EPIC Hub Team

For questions or suggestions, please refer to the development team.