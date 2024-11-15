### SASS/SCSS Guideline
https://sass-guidelin.es/#architecture

### SASS/SCSS Cheatsheet
https://devhints.io/sass

###Standard Variables
    vendor/shopware/storefront/Resources/app/storefront/src/scss/skin/shopware/abstract/variables

### Mixin Bootstrap
    vendor/shopware/storefront/Resources/app/storefront/node_modules/bootstrap/scss/mixins
    
### Partials
We use partials for performance reasons. These are files that are only compiled when they are imported.
Partials are marked with a `_` in their name. For example `_variables.scss`
