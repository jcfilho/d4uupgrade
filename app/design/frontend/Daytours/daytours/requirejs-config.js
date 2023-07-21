var config = {
    map: {
        'owl.carousel': 'js/owl.carousel',
        'slick': 'js/slick.min',
        'sticky-kit': 'js/jquery.sticky'
    },
    shim: {
        'js/owl.carousel':{
            'deps':['jquery']
        },
        'js/slick.min':{
            'deps':['jquery']
        },
        'js/jquery.sticky':{
            'deps':['jquery']
        }
    }
};
