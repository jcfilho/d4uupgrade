var config = {
	map: {
		'*': {
			productGallery: 'Magebay_Marketplace/js/product-gallery',
		    baseImage:          'Magebay_Marketplace/catalog/base-image-uploader',
			newVideoDialog:  'Magebay_Marketplace/js/new-video-dialog',
			openVideoModal:  'Magebay_Marketplace/js/video-modal',
			productAttributes:  'Magebay_Marketplace/catalog/product-attributes',
			groupedProduct: 'Magebay_Marketplace/js/grouped-product',
			//configurableAttribute:  'Magebay_Marketplace/catalog/product/attribute'
			mapChart: 'Magebay_Marketplace/js/chart',
            momentjs: 'Magebay_Marketplace/assets/global/plugins/moment',
            daterangepicker: 'Magebay_Marketplace/assets/global/plugins/bootstrap-daterangepicker/daterangepicker',
            "Magento_Customer/js/view/authentication-popup": "Magebay_Marketplace/js/view/authentication-popup",
            bootstrapjs: "Magebay_Marketplace/assets/global/plugins/bootstrap/js/bootstrap",
		}
	},
    bundles: {
        "Magebay_Marketplace/js/theme": [
            "modalPopup",
            "useDefault",
            "collapsable"
        ]
    }	
}