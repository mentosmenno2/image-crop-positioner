# WP-CLI commands

There are custom [WP-CLI](https://wp-cli.org/) commands available.

## Face detection

### Get face

Get face coordinates.

```sh
wp image-crop-positioner face-detection get-face "\path\to\input_image.jpg"
```

### Save image

Generate image with the face cropped out.

```sh
wp image-crop-positioner face-detection save-image "\path\to\input_image.jpg" "\path\to\output_image.jpg"
```

## Migrate

### Attachments

Run a migration for all attachments

```sh
wp image-crop-positioner migrate attachments regenerate_images --batch-size=100 --batch-number=1
wp image-crop-positioner migrate attachments my_eyes_are_up_here --batch-size=100 --batch-number=1
```
