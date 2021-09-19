[![GitHub Actions status](https://github.com/mentosmenno2/image-crop-positioner/workflows/Build%20%26%20test/badge.svg)](https://github.com/mentosmenno2/image-crop-positioner/actions)

# Image Crop Positioner WordPress plugin

This plugin allows you to better crop your images, based on facial detection or hotspot selection.

## In Development

> :warning: **This plugin is still in development.** Some functinality may be missing or not working as intended.

## Face detection method

The face detection is done in PHP.
It uses a modified version of the [softon/laravel-face-detect library](https://github.com/softon/laravel-face-detect).

## Installation
### Zip download (recommended)
To download and install this plugin in your WordPress website, follow the steps below.

1. Go to the [releases tab](https://github.com/mentosmenno2/image-crop-positioner/releases) and find the latest release.
2. Download the zip named `image-crop-positioner-x.x.x.zip` (where x.x.x is the version number).
3. Rename the downloaded zip to `image-crop-positioner.zip`.
4. Extract the zip file. Make sure it puts the contents in a directory called `image-crop-positioner`.
5. Put the extracted folder and it's contents inside the `wp-content/plugins` directory of your WordPress installation.
6. Activate the plugin via the wp-admin panel.

### Composer installation (alternative method)
It's also possible to download the plugin via Packagist.
Keep in mind that if you do, your composer should be setup in a way that packages of the type `wordpress-plugin` are installed in your `wp-content/plugins` directory.

## WP CLI commands

There are some WP-CLI commands available.

### Face detection

#### Get face

Get face coordinates.

```sh
wp image-crop-positioner face-detection get-face "\path\to\input_image.jpg"
```

#### Save image

Generate image with the face cropped out.

```sh
wp image-crop-positioner face-detection save-image "\path\to\input_image.jpg" "\path\to\output_image.jpg"
```

### Migrate

#### My Eyes Are Up Here

Migrate all attachment faces and hotspots from My Eyes Are Up Here to Image Crop Positioner

```sh
wp image-crop-positioner migrate my-eyes-are-up-here --per-page=100 --page=1
```

## Development
### Setup
1. Create a new WordPress installation using the latest twenty-* theme.
2. Navigate to the `wp-content/plugins` directory.
3. Clone this repo there, so it creates the `image-crop-positioner` directory.
4. Navigate to the `image-crop-positioner` directory, and from there, run:

```bash
composer install
npm ci
npm run build
```

Run `composer run test` to verify results locally (more info about tests later in this file).

### Releasing
1. Merge all PR's in the `master` branch.
2. Change the version number in `image-crop-positioner.php`.
3. Wait for all GitHub Actions to finish.
4. The plugin is built via GitHub actions in the `master-build` branch. Wait for it to finish.
5. Update the created draft release, set a tag and version name, in SEMVER structure like vX.X.X. Make sure to select `master-build` as the target branch.
6. Publish the release.

### Composer
Composer is used to manage the autoloading and automatic testing. More on the testing subject can be found later in this document.

### Webpack
#### Usage
  - `npm run start`
  - `npm run lint`
  - `npm run fix`
  - `npm run build`
  - `npm run scripts:lint`
  - `npm run scripts:fix`
  - `npm run styles:lint`
  - `npm run styles:fix`
  - `npm run browsersync`
  - `npm run bundle-analyzer`

If you run into any problems locally with the initial installation of the node_modules (especially webpack) try setting your local environment to DEV by executing the following on your CLI:

```bash
export NODE_ENV=development
```

The cause of the problem is probably the Node version and the default state it's in (it used to install all packages, but recently changed to production modules only).

#### Config
Using custom config for webpack dev server. Duplicate the `development/config.local.json.example` file and save it as `development/config.local.json`. In this file you add your own project url, set secure based on your dev protocol and set a port. This config is being used to overwrite the default URL of your local dev environment. This applies to the webpack-dev-server and browser-sync.

Example:
```json
{
  "url": "http://local.my-site.test",
  "secure": false,
  "port": 1234
}
```

#### Images
Webpack automatically processes images used in the SCSS. For the images that you use directly in php, import them into `index.js`.

```javascript
import 'images/logo.jpg';
```

Get the image URL in the .php files by using the `( new \Mentosmenno2\ImageCropPositioner\Assets() )->get_assets_directory_url()` function.
For example placing a logo in the header:
```html
<img src="<?php echo ( new \Mentosmenno2\ImageCropPositioner\Assets() )->get_assets_directory_url(); ?>/images/logo.jpg" alt="">
```

##### Lazyload
We use default browser lazyloading for images.
Usage example:
```html
<img src="<?php echo ( new \Mentosmenno2\ImageCropPositioner\Assets() )->get_assets_directory_url(); ?>/images/thumbnail.jpg" loading="lazy" alt="">
```

#### Linter
- Styleint: Styleint is used, rules are set in `.stylelintrc.json`. View all [doc rules](https://stylelint.io/user-guide/rules/list)
- JS: eslint is used, rules are set in `.eslintrc.json`. View all [doc rules](https://eslint.org/docs/rules/)

### Localization
Default text language for this repo is English.
Make sure you translate all strings with the text-domain `image-crop-positioner`.

Run: `composer run make-pot` for updating the .pot file.

### Automated testing
There are multiple code tests in place.
- Composer, to check if the composer files are intact
- PHPCS, to check if you are following the Level Level PHP code standards
- Psalm tests, that searches for possible mistakes in your code
- PHP Doc Check, to check if complicated functions have comments explaining the functionality

#### GitHub actions
On every pull-request, a GitHub action is run that verifies the project, based on the
`composer run test` results.

#### Manually

Run `composer run test` to verify tests locally.

```

#### Fixing

Run `composer run fix` to use automated fixing tools such as phpcbf.

### Working locally with https

If you locally work with https you need to enable Chrome to allow invalid certificates. To enable this go to: `chrome://flags/#allow-insecure-localhost` and enable the `Allow invalid certificates for resources loaded from localhost.` option.

### Editor config
Theme comes with a .editorconfig file. For this to work you need to install a plugin that uses the `.editorconfig` file.
- [Visual Code Studio](https://marketplace.visualstudio.com/items?itemName=EditorConfig.EditorConfig)
- [Atom](https://atom.io/packages/editorconfig)
