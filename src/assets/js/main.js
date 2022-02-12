$(document).ready(function(){

    //Animations
    AOS.init({
        startEvent: 'load',
        duration: 1200,
    });
    $(window).on('load', function() {
        AOS.refresh();
    });

    //Show mobile menu
    $('#menu-button').on('click', function(event) {
        $('.mobile-menu').slideToggle();
        //showHideMenu();
    });
    $('#mobile-menu-close').on('click', function(event) {
        $('.mobile-menu').slideToggle();
        //showHideMenu();
    });

    //Сhanging words on index page
    (function(){
        var words = [
            'Conversion Rate',
            'Mobile Presence',
            'Profitability',
            'Logistics',
            'Duties & Taxes',
            'User Experience',
        ], i = 0;
        setInterval(function(){
            $('#changingword').fadeOut(function(){
                $(this).html(words[i=(i+1)%words.length]).fadeIn();
            });
        }, 3000);
    })();



    var svg1 = new Vivus('my-svg', {duration: 200});
    var svg2 =new Vivus('my-svg2', {duration: 200});

     //var logoTest = new Vivus('my-svg', {duration: 1000, type:'sync', start:'manual'});
    // window.addEventListener('wheel', scrolling);
    // let progress = 0;
    // function scrolling(e){
    //     progress += e.deltaY / 1000;
    //     logoTest.setFrameProgress(progress);
    // }

    // Path svg animation
    var element = document.querySelector('#my-svg');
    var Visible = function (target) {
        var targetPosition = {
                top: window.pageYOffset + target.getBoundingClientRect().top,
                left: window.pageXOffset + target.getBoundingClientRect().left,
                right: window.pageXOffset + target.getBoundingClientRect().right,
                bottom: window.pageYOffset + target.getBoundingClientRect().bottom
            },
            windowPosition = {
                top: window.pageYOffset,
                left: window.pageXOffset,
                right: window.pageXOffset + document.documentElement.clientWidth,
                bottom: window.pageYOffset + document.documentElement.clientHeight
            };
        if (targetPosition.bottom > windowPosition.top &&
            targetPosition.top < windowPosition.bottom &&
            targetPosition.right > windowPosition.left &&
            targetPosition.left < windowPosition.right) {
            //console.clear();
            //console.log('You see block)');
            svg1.play();
        } else {
            //console.clear();
            svg1.reset();
        };
    };
    //On scroll event
    window.addEventListener('scroll', function() {
        Visible (element);
    });
    Visible (element);



    // Path svg animation
    var element2 = document.querySelector('#my-svg2');
    var Visible2 = function (target) {
        var targetPosition = {
                top: window.pageYOffset + target.getBoundingClientRect().top,
                left: window.pageXOffset + target.getBoundingClientRect().left,
                right: window.pageXOffset + target.getBoundingClientRect().right,
                bottom: window.pageYOffset + target.getBoundingClientRect().bottom
            },
            windowPosition = {
                top: window.pageYOffset,
                left: window.pageXOffset,
                right: window.pageXOffset + document.documentElement.clientWidth,
                bottom: window.pageYOffset + document.documentElement.clientHeight
            };
        if (targetPosition.bottom > windowPosition.top &&
            targetPosition.top < windowPosition.bottom &&
            targetPosition.right > windowPosition.left &&
            targetPosition.left < windowPosition.right) {
            //console.clear();
            //console.log('You see block)');
            svg2.play();
        } else {
            //console.clear();
            svg2.reset();
        };
    };
    //On scroll event
    window.addEventListener('scroll', function() {
        Visible2 (element2);
    });
    Visible2 (element2);
});




