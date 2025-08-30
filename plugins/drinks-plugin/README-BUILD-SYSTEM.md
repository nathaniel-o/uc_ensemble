# Drinks Plugin - Modern Build System

This plugin now uses WordPress's recommended `@wordpress/scripts` build system for modern block development.

## 🚀 Quick Start

### Prerequisites
- Node.js (version 14 or higher)
- npm or yarn

### Installation
```bash
cd wp-content/plugins/drinks-plugin
npm install
```

### Development
```bash
# Start development server with hot reload
npm run dev

# Or use the start command (same as dev)
npm run start
```

### Production Build
```bash
# Build optimized assets for production
npm run build
```

## 📁 Project Structure

```
drinks-plugin/
├── src/                    # Source files
│   ├── index.js           # Main entry point (block editor)
│   ├── frontend.js        # Frontend functionality
│   └── style.css          # All styles (editor + frontend)
├── build/                 # Built assets (generated)
├── js/                    # Legacy files (fallback)
├── css/                   # Legacy files (fallback)
├── package.json           # Dependencies and scripts
├── .gitignore            # Git ignore rules
└── drinks-plugin.php      # Main plugin file
```

## 🔧 Available Scripts

| Command | Description |
|---------|-------------|
| `npm run dev` | Start development server with hot reload |
| `npm run build` | Build optimized assets for production |
| `npm run start` | Same as `npm run dev` |
| `npm run lint:js` | Lint JavaScript files |
| `npm run lint:css` | Lint CSS files |
| `npm run format` | Format code with Prettier |
| `npm run packages-update` | Update WordPress packages |

## 🎯 Key Features

### Modern Development
- **Hot Reload**: See changes instantly in the browser
- **ES6+ Support**: Use modern JavaScript features
- **CSS Processing**: Automatic CSS optimization
- **Source Maps**: Better debugging experience

### WordPress Integration
- **@wordpress/scripts**: Official WordPress build tool
- **WordPress Packages**: Use `@wordpress/element`, `@wordpress/components`, etc.
- **Block Editor API**: Full integration with Gutenberg
- **Internationalization**: Built-in i18n support

### Production Ready
- **Asset Optimization**: Minified and optimized output
- **Tree Shaking**: Remove unused code
- **Code Splitting**: Efficient loading
- **Fallback Support**: Works with or without build

## 🔄 Migration from Traditional Approach

The plugin now supports both approaches:

1. **Modern Build System** (Recommended)
   - Use `npm run dev` for development
   - Use `npm run build` for production
   - Edit files in `src/` directory

2. **Traditional Approach** (Fallback)
   - Edit files directly in `js/` and `css/` directories
   - No build process required
   - Works immediately

## 🚨 Important Notes

- **Build Required**: For production, run `npm run build` to generate optimized assets
- **Fallback Support**: The plugin automatically falls back to source files if build doesn't exist
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **Block Development**: Optimized for Gutenberg block development

## 🛠️ Troubleshooting

### Build Issues
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear build directory
rm -rf build/
npm run build
```

### Development Issues
```bash
# Check for linting errors
npm run lint:js
npm run lint:css

# Format code
npm run format
```

### WordPress Integration
- Ensure the plugin is activated in WordPress
- Check browser console for any JavaScript errors
- Verify that build files are being loaded correctly

## 📚 Resources

- [@wordpress/scripts Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/)
- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
