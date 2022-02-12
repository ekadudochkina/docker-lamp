/**
 * Главный класс библиотеки home-studio.
 * Обеспечивает наиболее часто используемые функции
 * 
 * @constructor
 */
function HomeStudio()
{
    var version = "0.0.1";
    
    var self = this;

    /**
     * Параметры переданные от сервера
     * 
     * @type json
     */
    var params = {};
    
    /**
     * Слушатели событий
     * 
     * @type json
     */
    var listeners = {};
    
    /**
     * Конструктор
     * 
     * @private
     * @returns {undefined}
     */
    var construct = function ()
    {
        console.log("Home-Studio library initializing.");
        initParams.call(self);
        var msg = "Home-Studio initialized: v" + version;
        msg += ". jQuery version: " + $.fn.jquery;
        console.log(msg, self);
        
        this.showServerSideMessages();
    }

    /**
     * Ищет параметры переданные от сервера и добавляет их в массив параметров.
     * 
     * @private
     * @returns {Bool} True, если параметры были обнаружены
     */
    var initParams = function ()
    {
        if (window.clientParams)
        {
            params = window.clientParams;
            return true;
        }
        return false;
    }

    /**
     * Добавляет куки в браузер.
     * 
     * @param {String} name Имя 
     * @param {String} value Значение
     * @param {Number} expires Сколько секунд должна держаться кука
     */
    var setCookie = function (name, value, expires)
    {
        if (!$.cookie)
            throw new Error("jquery.cookie.js is required to use cookie functions. ");

        var options = {};
        if (expires !== undefined)
        {
            var date = new Date();
            date.setSeconds(date.getSeconds() + expires);
            options.expires = date;
        }
        options.path = "/"
        $.cookie(name, value, options);
    };

    /**
     * Получает куки
     * 
     * @param {String} name Имя куки
     * @returns {String} Значение куки
     */
    var getCookie = function (name)
    {
        if (!$.cookie)
            throw new Error("jquery.cookie.js is required to use cookie functions. ");
        var val = $.cookie(name);
        return val;
    }

    /**
     * Удаляет куки 
     * 
     * @param {String} name Имя куки
     */
    var deleteCookie = function (name)
    {
        if (!$.cookie)
            throw new Error("jquery.cookie.js is required to use cookie functions. ");
        $.removeCookie(name);

    }

    /**
     * Получение асболютного URL вебсайта. 
     * 
     * Создается как правило сервером. От этого URL можно отталкиваться на любой странице.
     * @returns {String} Url страницы
     */
    var getAbsoluteUrl = function ()
    {
        return self.getServerParam("absoluteUrl");
    };

    /**
     * Получение параметра от сервера.
     * 
     * @param {Sting} name Имя параметра
     * @returns {Object} Значение параметра
     */
    var getServerParam = function (name)
    {
        if (params[name] !== undefined)
            return params[name];

        return null;
    };

    this.showErrorMessage = function (message,callback)
    {
        this.showMessage(message, "danger",callback);
    }
    
    this.showSuccessMessage = function (message,callback)
    {
        this.showMessage(message, "success",callback);
    }

    this.showMessage = function (message, type,callback)
    {
        //Таймауты нужны для того, чтобы можно было сделать красивую анимацию c CSS
        setTimeout(
                function () {
                    var alertsDiv = $(".alerts").get(0);
                    console.log(alertsDiv);
                    if (!alertsDiv)
                    {
                        alertsDiv = $("<div/>");
                        $(alertsDiv).addClass("alerts");
                        $("body").prepend(alertsDiv);
                        console.log(1);
                    }

                    var classes = "alert alert-" + type;
                    var div = $("<div/>");
                    $(div).addClass(classes);
                    $(div).html(message);
                    var close = $("<div/>");
                    $(close).addClass("close");
                    $(close).on("click",function(){$(this).parent(".alert").fadeOut(); });
                    $(div).append(close);
                    
                  
                    
                    $(alertsDiv).prepend(div);
                    setTimeout(function () {
                        $(".alerts .alert").addClass("shown");
                        if(callback)
                        {
                            callback();
                        }
                    });
                });
    }
   
    this.showServerSideMessages = function()
    {
        var messages = this.getServerParam("errorMessages");
        if(messages)
        {
        for(var i =0; i < messages.length; i++)
        {
            this.showErrorMessage(messages[i]);
        }
        }
       
        var messages = this.getServerParam("successMessages");
        if(messages)
        {
        for(var i =0; i < messages.length; i++)
        {
            this.showSuccessMessage(messages[i]);
        }
        }
        //console.log(messages);
    }
    
    this.params = params;
    this.setCookie = setCookie;
    this.getCookie = getCookie;
    this.deleteCookie = deleteCookie;
    this.getAbsoluteUrl = getAbsoluteUrl;
    this.getServerParam = getServerParam;
    
    this.on = function(eventName,callback)
    {
        if(!listeners[eventName])
        {
            listeners[eventName] = [];
        }
        listeners[eventName].push(callback);
    }
    
    this.fireEvent = function(eventName,params)
    {
        console.log("Fire: '"+eventName+"'");
        if(listeners[eventName])
        {
           //console.log(listeners);
           for(var i =0; i < listeners[eventName].length; i++)
           {
               
               listeners[eventName][i](params);
           }
        }
    }
   this.render = function(view,data)
    {
        var obj = data;
        var template = view;

        for(var field in obj)
        {
            //console.log(field);
            var val = obj[field] === null ? "" : obj[field];
            template = template.replace(new RegExp("{{"+field+"}}","g"),val);
        }
        return template;

    }
    construct.call(this);
};


hs = new HomeStudio();
