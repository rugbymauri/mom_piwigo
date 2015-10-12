jQuery(function(){

    jQuery('#camera_wrap').camera({
        //height: '927px',
        height: camereHeight,
        loader: 'bar',
        pagination: false,
        thumbnails: true,
        fx: 'simpleFade',
    });
});
