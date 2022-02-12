/**
 * Объект параллакса
 * 
 * @returns {Parallax}
 */
function HsParallax()
{
    /**
     * Набор элементов, участвующих в параллаксе
     * 
     * @type Object
     */
    this.elements = [];

    /**
     * Глобальный отсут сверху для всех элементов параллакса.
     * Удобно, когда сверху есть меню с position: fixed
     * @type Integer
     */
    this.globalVerticalOffset = 0;

    /**
     * Высота окна браузера
     * @type Integer
     */
    this.viewport = 0;

    /** 
     * Адаптивный паралакс
     * @type Boolean
     */
    this.responsive = false;
    
    /**
     * Если True, то паралакс будет применять только в видимым элементам
     * @type Bool
     */
    this.processViewportOnly = false;
    
    /**
     * Флаг того, что параллакс запущен
     * @type Boolean
     */
    var started = false;

    /**
     * Функция обновления параллакса. Заново пересчитывает элементы и кеширует данные.
     */
    this.refresh = function () {

        var self = this;
        clear();
        this.elements = [];
        this.viewport = $(window).height();

        $("*[data-stellar-ratio]").each(function (i, el) {

            var rate = $(el).attr("data-stellar-ratio");
            var offsetParent = $(el).parents("*[data-stellar-offset-parent]").get(0);
            var parentOffset = $(offsetParent).offset().top;
            
            $(el).css("position", "relative");
            
            
            var offset = parentOffset - self.globalVerticalOffset;

            //Кешируем необходимые данные в объект и добавляем его в массив
            var obj = {};
            obj.element = el;
            obj.parallaxOffset = offset;
            obj.parentOffset = parentOffset;
            obj.height = $(el).height();
            obj.originalTop = $(el).css("top");
            obj.rate = rate;
            obj.jElement = $(el);
            obj.offsetParent = offsetParent;
            
            self.elements.push(obj);
        });
        scrollHandler();
    }.bind(this);

    /**
     * Запускает параллакс
     * @return {Bool} True, если объект не работал и был запушен
     */
    this.start = function ()
    {
        if (started)
            return false;

        started = true;
        $(window).on("scroll", scrollHandler);
        this.refresh();

        if (this.responsive)
            $(window).on("resize", resizeHandler);
        return true;
    }.bind(this);

    /**
     * Останавливает параллакс. 
     * Возвращает элементы на оригинальные позиции
     * @return {Bool} True, если объект работал и был остановлен
     */
    this.stop = function ()
    {
        if (!started)
            return false;

        started = false;
        $(window).off("scroll", scrollHandler);
        $(window).off("resize", resizeHandler);
        clear();
        return true;
    }.bind(this);

    /**
     * Восстановление элементов на первоначальные позиции
     * @type Function
     */
    var clear = function ()
    {
        for (var i = 0; i < this.elements.length; i++)
        {
            var obj = this.elements[i];
            $(obj.element).css("top", obj.originalTop);
        }
    }.bind(this);

    /**
     * Обрабатывает событие скролла
     * @type Function
     */
    var resizeHandler = function ()
    {
        console.log("Adjusting parallax on resize");
        this.refresh();
    }.bind(this);

    /**
     * Обрабатывает событие скролла
     * @type Function
     */
    var scrollHandler = function ()
    {
        var scroll = $(document).scrollTop();

        //Будем выкидывать элементы, которых нет на экране
        var maxVisibleOffset = this.viewport + scroll + this.globalVerticalOffset;
        var minVisibleOffset = scroll + this.globalVerticalOffset;

        //Добавляем доп на всякий
        var delta = 20;
        maxVisibleOffset += delta;
        minVisibleOffset -= delta;
        for (var i = 0; i < this.elements.length; i++)
        {
            var element = this.elements[i];
            if (this.processViewportOnly)
            {
                var upperBorder = element.offset;
                var lowerBorder = element.offset + element.height;
                //console.log(i,upperBorder,maxVisibleOffset,lowerBorder,minVisibleOffset);   
                if (upperBorder > maxVisibleOffset || lowerBorder < minVisibleOffset)
                    continue;
            }
            var newpos = (element.parallaxOffset - scroll) * element.rate;
            element.jElement.css("top", newpos);
            //console.log("updated",i);
        }
    }.bind(this);
}