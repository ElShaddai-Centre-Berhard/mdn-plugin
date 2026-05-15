( function () {
	document.addEventListener( 'DOMContentLoaded', function () {
		var radios = document.querySelectorAll( 'input[name="mdn_page_mode"]' );
		if ( ! radios.length ) return;

		function toggleSections( mode ) {
			document.getElementById( 'mdn-page-mode-auto' ).style.display   = mode === 'auto'   ? '' : 'none';
			document.getElementById( 'mdn-page-mode-manual' ).style.display = mode === 'manual' ? '' : 'none';
		}

		radios.forEach( function ( radio ) {
			radio.addEventListener( 'change', function () {
				toggleSections( this.value );
			} );
		} );
	} );
} )();
