# Developing

Amazing that you want to see how you can help developing this plugin.
This page has some instructions to get you started.

## Requirements

- PHP >= 7.3
- NodeJS 12.x.x
- MySQL 5.7.x

## Setup

1. Create a new WordPress installation using the latest twenty-* theme.
2. Navigate to the `wp-content/plugins` directory.
3. Clone this repo there, so it creates the `image-crop-positioner` directory.
4. Navigate to the `image-crop-positioner` directory.
5. Run the commands below.
```bash
composer install
npm ci
npm run build
```

You can verify your installation by running some tests. See the [Automted testing chapter](#automated-testing).

## Composer

Composer is used in this project, for autoloading and for devdependencies.
Composer should not be used for normal dependencies, as this can cause conflicts with other plugins.

## NPM / NodeJS

NPM and NodeJS are used to build assets.
It converts our stylesheets from `SCSS` to `CSS` files.
It also compiles our Javascript.

If you run into any problems locally with the initial installation of the node_modules (especially webpack) try setting your local environment to DEV by executing the following on your CLI:

```sh
export NODE_ENV=development
```

### Config
You can use a webpack dev server for watching asset changes.
You will need to configure it before you're able to use it.
Duplicate the `development/config.local.json.example` file and save it as `development/config.local.json`.
In this file you add your own project url, set secure based on your dev protocol and set a port.
This config is being used to overwrite the default URL of your local dev environment.
This applies to the webpack-dev-server and browser-sync.

Example:
```json
{
  "url": "http://local.my-site.test",
  "secure": false,
  "port": 1234
}
```

### Using images
Webpack automatically processes images used in the SCSS. For the images that you use directly in php, import them into `index.js`.

```javascript
import 'images/logo.jpg';
```

Get the image URL in the .php files by using the `( new \Mentosmenno2\ImageCropPositioner\Assets() )->get_assets_directory_url()` function.
For example placing a logo in the header:
```html
<img src="<?php echo ( new \Mentosmenno2\ImageCropPositioner\Assets() )->get_assets_directory_url(); ?>/images/logo.jpg" alt="">
```

#### Lazyload
We use default browser lazyloading for images.
Usage example:
```html
<img src="<?php echo ( new \Mentosmenno2\ImageCropPositioner\Assets() )->get_assets_directory_url(); ?>/images/thumbnail.jpg" loading="lazy" alt="">
```

### Localization
Default text language for this repo is English.
Make sure you translate all strings with the text-domain `image-crop-positioner`.

You can generate a pot file using the following command:
```sh
composer run make-pot
```

## Editor config
This plugin comes with a .editorconfig file. For this to work you need to install a plugin that uses the `.editorconfig` file.
- [Visual Code Studio](https://marketplace.visualstudio.com/items?itemName=EditorConfig.EditorConfig)
- [Atom](https://atom.io/packages/editorconfig)

## Automated testing

Please read the chapter for [automated code tests](./automated-testing.md) to see how you can test your code.

## Releasing a new version
1. Merge all PR's in the `master` branch.
2. Change the version number in `image-crop-positioner.php`.
3. Wait for all GitHub Actions to finish.
4. The plugin is built via GitHub actions in the `master-build` branch. Wait for it to finish.
5. Update the created draft release, set a tag and version name, in SEMVER structure like vX.X.X. Make sure to select `master-build` as the target branch.
6. Publish the release.
