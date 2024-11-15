# HbH Default Theme Informations
## General Theme Settings
- `shopware/custom/static-plugins/HbHProjectMainTheme/composer.json`
    - do not change any settings of this theme. Copy this Template to your Project folder and do Project specific settings.
- `shopware/custom/static-plugins/HbHProjectMainTheme/src/Resources/theme.json`
    - here you can see the theme structure, how and where which files are implemented.

## Twig Structure
`shopware/custom/static-plugins/HbHProjectMainTheme/src/Resources/views/storefront`

The structure of the folder is already determined, so that only the files have to be created in the right place.

If you are unsure, here is the [Docs](https://twig.symfony.com/doc/3.x/)

## SASS // SCSS Structure   
`shopware/custom/static-plugins/HbHProjectMainTheme/src/Resources/app/storefront/src/scss`

Information about SASS // SCSS Structure 

The structure of the folder is already determined, so that only the files have to be created in the right place.
After creation, the file must be imported into the base.scss.

Please make sure that the file is created in the right place so that the code is easier to understand later.
If you are unsure, here are the best patterns from [SASS // SCSS.](https://sass-guidelin.es/#architecture)

###Use Variables
Create global variables in `abstracts/_variables.scss`.
###Bootstrap
Documentation [here](https://getbootstrap.com/docs/5.0/customize/sass/)

Try to use Bootstrap as much as possible to keep the code as clean and neat as possible.
The Bootstrap Mixins can be reached under the following folder structure:
`vendor/shopware/storefront/Resources/app/storefront/node_modules/bootstrap/scss/mixins`


## Jquery Structure
The existing Javascript can be extend/overwrite by
`HbHProjectMainTheme/src/Resources/app/storefront/src/script`
There you can create your own `example.js` plugin, where youre function/logic is implemented.
Finally you have to load the code from your plugin into `main.js` file.

With the `FindPluginName` Plugin you can find out, which plugins are used by an element.

Documentation [here](https://docs.shopware.com/en/shopware-platform-dev-en/theme-guide/javascript)

## Assets Structure
`shopware/custom/static-plugins/HbHProjectMainTheme/src/Resources/app/storefront/src/assets`

This is where the assets are implemented. After adding a new asset, the command `bin / console assets:install` must be executed via the CLI.

Now the assets are installed and can be integrated in the Twig / SCSS.
How to implemented this in the shop you can be see [here.](https://docs.shopware.com/en/shopware-platform-dev-en/developer-guide/storefront/assets)

## Snippets Structure

Text modules can be implemented here.

When deploying to Stage / Live, the created snippets are created automatically.
Any language can be added here.

To import snippets about the file, check the .env file. APP_ENV must contain "dev".

- `shopware/custom/static-plugins/HbHProjectMainTheme/src/Resources/snippet`
    - Snippet structure where the text modules of the languages can be maintained.

- `shopware/custom/static-plugins/HbHProjectMainTheme/src/Resources/config`
    - in services.xml a new Language must be implement by code example inline.

You can find a Shopware documentation [here](https://docs.shopware.com/en/shopware-platform-dev-en/theme-guide/snippets))
