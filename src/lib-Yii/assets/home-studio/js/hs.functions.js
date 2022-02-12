/**
 * Home-studio library.
 * Полезные функции для разработки вебсайтов.
 * 
 * @author Sarychev Aleksey <freddis336@gmail.com>
 * @link http://home-studio.pro
 */


/**
 * Увеличивает HTML элемент до размера окна браузера. Реагирует на изменение окна браузера.
 * 
 * @author Sarychev Aleksey
 * @param {HTMLElement} element Элемент страницы
 * @param {Number} offset  Отступ от высоты окна. Например, если нужно оставить немного пространства
 * @param {Number} minsize Минимальная высота элемента на странице.
 * @param {Bool} onResize Если true, то элемент будет также реагировать на изменение окна браузера
 */
function resizeElementToViewPort(element, offset, minsize, onResize) 
{

    minsize = minsize != null ? minsize : 465;

    var height = $(window).height() + offset;


    height = height > minsize ? height : minsize;

    //console.log("resize", height);
    $(element).css("height", height + "px");
    if (onResize)
    {
        $(window).on("resize", function () {
            resizeElementToViewPort(element, 0, 300);
        });
    }

}


/**
 * Активация скролинга внутри страницы. 
 * 
 * Если у элемента есть класс .scroll и задан аттрибут data-scroll=".selector", то при клике на этот элемент
 * экран будет переведен на целевой элемент. Целевой элемент находится по селектору, указанному в аттрибуте data-scroll
 * 
 * 
 * Скроллинг управляется HTML аттрибутами:
 * data-scroll - содержит в себе селектор элемента на который должен наводиться экран
 * data-scroll-offset - содержит в себе количество пикселей на который надо отступить при наведении экрана на элемент
 * data-scroll-duration - время за которое должен произойти скроллинг (чем дальше элемент, тем больше должно быть время)
 * data-scroll-feedback - Если true, то при появлении целевого элемента на экране у кнопки будет меняться класс на current
 * 
 * @author Sarychev Aleksey
 * @example 
 * <pre>
 *  <div class='menu fixed'>
 *      <div class='bullet scroll' data-scroll='.b-home' data-scroll-duration='1000' data-scroll-feedback='true' >
 *          <h6>Top</h6>
 *      </div>
 *      <div class='bullet scroll' data-scroll='.b-intro' data-scroll-duration='2000' data-scroll-offset='200' data-scroll-feedback='true'>
 *          <h6>Development</h6>
 *      </div>
 *      <div class='bullet scroll' data-scroll='.b-portfolio' data-scroll-duration='3000' data-scroll-offset='-50' data-scroll-feedback='true'>
 *           <h6>Portfolio</h6>
 *      </div>
 * </pre>
 */
function activateScrolling()
{
    if(!$.scrollTo)
        throw new Error("Необхоидмо подключить библиотеку jquery.scrollTo");

    var scrollClass = "current";
//    //Флаг, что был назначен текущий элемент по-умолчанию
//    var feedbackDefaultAdded = false;

    $(".scroll").each(function (i, el) {

        //Получаем переменные:

        //Селектор для поиска целевого элемента
        var sel = $(this).attr("data-scroll");
        if (!sel)
            throw new Error("You need to set 'data-scroll' attribute with selector for html element to scroll to.");
        //Целевой элемент, который должен появиться на экране
        var target = $(sel).get(0);

        //Отступ вьюпорта от верхушки элемента целевого элемента
        var targetOffset = $(this).attr("data-scroll-offset");
        targetOffset = targetOffset ? parseInt(targetOffset) : 0;
        //console.log(targetOffset);
        //Сколько времени экран будет подъезжать к элементу
        var duration = $(this).attr("data-scroll-duration");
        duration = duration ? duration : 1200;

        //Целевой элемент, который должен появиться на экране
        var target = $(sel).get(0);

        //Функция устанавливает класс текущего элемента меню
        //Если html элемент находится на экране
        var setElementClass = function (sel, el, offset)
        {

            $(el).parent().children("*").removeClass(scrollClass);
            $(el).addClass(scrollClass);

            //дебаг (рисует линии событий)
            if (false) {
                console.log("section '", sel, "'", target, this);
                var top = $(target).position().top + offset;
                var color = top < $(target).position().top ? "green" : "red";
                $("body").append("<div style='position:absolute; z-index:32000; color:" + color + "; left:0px; width:100%; background:" + color + "; height:2px;top:" + top + "px;'>" + sel + "</div>");
            }
        }

        //Обратная связь с меню (если это меню)  
        var feedback = $(this).attr("data-scroll-feedback") == "true";
        if (feedback)
        {
            if (!window.Waypoint)
                throw Error("Необходимо подключить Waypoints.js для работы обратной связи скроллинга.");

            //Добавляем класс поумолчанию первому пункту меню
            //В целом, если все настроено правильно этот код лишний
//            if (!feedbackDefaultAdded)
//            {
//                $(this).addClass(scrollClass);
//                feedbackDefaultAdded = true;
//            }


            //ОСНОВНАЯ ПРОБЛЕМА НАХОДИТСЯ ТУТ
            //все дело в том, что Waypoints.js определяет появление элемента на экране математически
            //если вьюпорт задел верхнюю часть элемента + отступ
            //а наш мозг делает это более умным образом - мы определяем не по вьюпорту, а по элементу - какой элемент
            //занимает наибольшую часть экрана - такой в нем и находится

            //То есть в большинстве случаев нас интересует не ПОЯВЛЕНИЕ, а ФОКУС!
            //Появление плохо работает с движением. Если мы установим отступ на 50% от вьюпорта, то 
            //все будет хорошо работать при движение вниз, кроме последнего элемента - так как если он не большой, то его
            //экран не дойдет до 50% отметки. 
            //Если же сделать наоборот, и поставить отрицательный отступ, то проблема будет с движением в другую сторону
            //Вышеописанное усложняет работу с меню, так как первый и последний пункты работают криво, да и остальные тоже не очень


            //Еще одна проблема то в том, что у каждого элемента есть 1 линия появления на экране, а повяться он может 2 двух
            //направлениях. Это также создает ситуации, когда при движении в одном направлении элемент едва ли виден на экране,
            //а в другом нужно его полностью промотать, чтобы событие сработало

            //В общем хорошее решение в том, чтобы на каждом элементе нарисовать 2 линии. 
            //Пол умолчанию элемент на 50% должен быть на экране, но не более 50% вьюпорта
            //На верхней линии 50% нижней части вьюпорта занимает элемент, а на нижней 50% верхней части
            //Одна будет срабатывать при движении вверх, вторая при движении вниз
            //Самое главное, чтобы линии смежных элементов не пересекались, иначе будет балаган
            //для этого есть небольшой маргин между смежными линиями и ограничение на 50% вьюпорта


            //Почему-то если делать маленький маргин тут, то плохо определяются линии
            var marginBetweenLines = 150;
            var viewport = $(window).height();

            var offset = 0;
            // offset -= -$(window).height() / 2;
            offset += $(target).height() / 2;
            offset = offset <= $(target).height() - viewport / 2 - marginBetweenLines ? $(target).height() - viewport / 2 - marginBetweenLines : offset;

            var options = {
                element: target,
                handler: setElementClass.bind(this, sel, el, offset),
                offset: offset,
                continious: true,
            };
            var waypoint = new Waypoint(options);


            offset = 0;
            //  offset += $(window).height() / 2;
            offset -= $(target).height() / 2;
            offset = offset <= viewport / -2 + marginBetweenLines ? viewport / -2 + marginBetweenLines : offset;
            var options2 = {
                element: target,
                handler: setElementClass.bind(this, sel, el, offset),
                offset: offset,
                continious: true,
            };
            var waypoint2 = new Waypoint(options2);
        }

        $(el).on("click", function (obj,offset) {
            
            $(window).scrollTo(obj, 1200, {offset: offset + 1}, 1200);
            return false;
        }.bind(this,target,targetOffset));

    });
}
