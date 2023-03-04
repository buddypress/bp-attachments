/**
 * Crops an Image for the Community Media directory.
 *
 * Credits: https://pqina.nl/blog/cropping-images-to-an-aspect-ratio-with-javascript/
 *
 * @since 1.0.0
 *
 * @param {string} url The source image URL.
 * @param {number} aspectRatio The aspect ratio to apply.
 * @param {number} maxWidth The maximum width to check.
 * @param {number} aspectRatio The maximum height to check.
 * @return {Promise<HTMLCanvasElement>} A Promise that resolves with the resulting image as a canvas element.
 */
function crop( url, aspectRatio, maxWidth, maxHeight ) {
	return new Promise(
		( resolve ) => {
			const inputImage = new Image();

			inputImage.onload = () => {
				const inputWidth = inputImage.naturalWidth;
				const inputHeight = inputImage.naturalHeight;
				const inputImageAspectRatio = inputWidth / inputHeight;

				if ( inputImage.naturalHeight <= maxHeight || inputImage.naturalWidth <= maxWidth ) {
					resolve(
						{
							src: inputImage.src,
							className: inputImage.naturalHeight <= maxHeight ? 'bp-full-width' : 'bp-full-height',
						}
					);
				} else {
					let outputWidth = inputWidth;
					let outputHeight = inputHeight;

					if ( inputImageAspectRatio > aspectRatio ) {
						outputWidth = inputHeight * aspectRatio;
					} else if ( inputImageAspectRatio < aspectRatio ) {
						outputHeight = inputWidth / aspectRatio;
					}

					const outputX = ( outputWidth - inputWidth ) * 0.5;
					const outputY = ( outputHeight - inputHeight ) * 0.5;
					const outputImage = document.createElement( 'canvas' );

					outputImage.width = outputWidth;
					outputImage.height = outputHeight;

					const ctx = outputImage.getContext( '2d' );
					ctx.drawImage(inputImage, outputX, outputY);

					resolve(
						{
							src: outputImage.toDataURL( 'image/png' ),
							className: 'bp-cropped-image',
						}
					);
				}
			};

			inputImage.src = url;
		}
	);
}

export default crop;
