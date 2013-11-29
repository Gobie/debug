/*jslint es5: true, forin: true, plusplus: true, unparam: true, browser: true, indent: 4 */
/**
 * es5 - ES5 syntaxe je povinně vynucena
 * forin - Nefiltrovaný "for in" je povolen, protože JSLint nedokáže rozpoznat, že volání hasOwnProperty je maskováno.
 *         Nemělo by být použito, může zakrýt opravdové chyby.
 * plusplus - Operátory ++/-- jsou povoleny, protože jsou čitelné.
 * unparam - Nepoužité parametry jsou použity kvůli undefined v obalující funkci kvůli ošetření možného přetížení.
 *           Nemělo by být použito, může zakrýt opravdové chyby.
 * browser - Browser je povolen, protože Debugger je exportován do window.
 */

/**
 * @fileOverview Debugger.
 * @author Michal Brašna
 * @version 3.4
 */

/**
 * Vytvoří Debugger a vyexportuje ho do window.
 * Zapouzdřuje inicializaci Debuggeru a vše kolem něj.
 *
 * @module Debugger
 * @param {Object} window
 * @param {undefined} [undefined]
 * @example V callbacku AJAXu zavolat při použití AjaxTemplate
 * Debugger.loadFromXHR(xhr);
 *
 * @example Ve stránce zavolat, kde message je z JsTemplate
 * Debugger.addMessage(message);
 * ...
 * Debugger.render();
 * @requires Zlib
 * @requires Base64Binary
 */
(function (window, undefined) {
    "use strict";
    var
    // Prerekvizity
        Zlib = window.Zlib || {},
        Base64Binary = window.Base64Binary || {},
    // Dokument
        document = window.document,
    // Aliasy základních funkcí
        core_create = Object.create,
        core_toString = Object.prototype.toString,
        core_hasOwnProperty = Object.prototype.hasOwnProperty,
        core_slice = Array.prototype.slice,
        core_map = Array.prototype.map,
        core_each = Array.prototype.forEach,
        core_every = Array.prototype.every,
        core_filter = Array.prototype.filter,
        core_indexOf = Array.prototype.indexOf,
    // Použité objekty
        utils, pubsub, element, cookie, filterer, templater, debug,
        message, startMessage, toggableMessage, errorMessage, dumpMessage, sqlErrorMessage, sqlQueryMessage,
    // Lokální instance debugu
        debugInstance;

    utils = (function () {
        /**
         * Utilitní objekt.
         *
         * @name Utils
         * @class
         * @inner
         */
        return {
            /**
             * Vrátí funkci s daným kontextem.
             * Dodatečné argumenty budou před argumenty, se kterými bude výstupní funkce volána.
             *
             * @param {Function} fn Funkce
             * @param {Object} scope Kontext
             * @param {...Object} Argumenty
             * @returns {Function}
             * @memberOf Utils
             */
            proxy: function (fn, scope) {
                var args = core_slice.call(arguments, 2);
                return function () {
                    return fn.apply(scope, args.concat(core_slice.call(arguments)));
                };
            },
            /**
             * Vrátí, zda je argument pole.
             *
             * @param {Object} arg Argument
             * @memberOf Utils
             */
            isArray: function (arg) {
                return core_toString.call(arg) === "[object Array]";
            },
            /**
             * Vrátí, zda je argument string.
             *
             * @param {Object} arg Argument
             * @memberOf Utils
             */
            isString: function (arg) {
                return core_toString.call(arg) === "[object String]";
            }
        };
    }());

    pubsub = function () {
        var PubSub = function () {
            return new PubSub.fn.init();
        };

        PubSub.prototype = {
            constructor: PubSub,
            /**
             * Pub/Sub implementace.
             * @constructs
             */
            init: function () {
            },
            /**
             * Úložiště pro observery, pod klíčem, což je topic.
             *
             * @type {Object}
             * @private
             */
            cache: {},
            /**
             * Zavolá všechny observery zaregistrované pod daným tématem.
             *
             * @param {String} topic Téma
             * @param {Array} [args] Pole argumentů pro observery
             * @param {Object} [scope] Kontext
             */
            publish: function (topic, args, scope) {
                if (!this.cache[topic]) {
                    return;
                }

                var self = this;
                core_each.call(this.cache[topic], function (handle) {
                    handle.apply(scope || self, args || []);
                });
            },
            /**
             * Zaregistruje observer pod dané téma.
             *
             * @param {String} topic Téma
             * @param {Function} callback Observer
             * @returns {Array} Identifikátor
             */
            subscribe: function (topic, callback) {
                this.cache[topic] = this.cache[topic] || [];
                this.cache[topic].push(callback);
                return [topic, callback];
            },
            /**
             * Odregistruje observer dle identifikátoru.
             *
             * @param {Array} handle Identifikátor z {@link PubSub#subscribe}
             */
            unsubscribe: function (handle) {
                if (utils.isString(handle)) {
                    this.cache[handle] = [];
                    return;
                }

                var topic = handle[0];
                if (!this.cache[topic]) {
                    return;
                }

                this.cache[topic] = core_filter.call(this.cache[topic], function (cb) {
                    return cb !== handle[1];
                });
            }
        };

        /**
         * Alias prototypu.
         * @var PubSub
         */
        PubSub.fn = PubSub.prototype;
        PubSub.fn.init.prototype = PubSub.fn;

        return PubSub;
    }();

    element = function () {
        var Element = function (el) {
            return new Element.fn.init(el);
        };

        Element.prototype = {
            constructor: element,
            /**
             * Uzel, nad kterým se pracuje.
             *
             * @type {HtmlElement}
             * @private
             */
            element: null,
            /**
             * Vrátí nacachovaný regexp pro matchování CSS třídy.
             *
             * @param {String} className Název CSS třídy
             * @returns {RegExp}
             * @function
             * @private
             */
            getRegexp: (function () {
                var regexps = {};
                return function (className) {
                    regexps[className] = regexps[className] || new RegExp("\\s*\\b" + className + "\\b", "g");
                    return regexps[className];
                };
            }()),
            /**
             * HTML modul pro manipulaci se styly a třídami.
             * Uloží si uzel pro pozdější manipulaci.
             *
             * @constructs
             * @param {HtmlElement} element Uzel
             */
            init: function (element) {
                this.element = element;
            },
            /**
             * Přidá CSS třídu k elementu.
             *
             * @param {String} className CSS třída
             * @returns {Element}
             */
            addClass: function (className) {
                if (this.hasClass(className)) {
                    return this;
                }
                var cn = this.element.className;
                this.element.className = (cn ? cn + ' ' : '') + className;
                return this;
            },
            /**
             * Odebere CSS třídu z elementu.
             *
             * @param {String} className CSS třída
             * @returns {Element}
             */
            removeClass: function (className) {
                this.element.className = this.element.className.replace(this.getRegexp(className), '');
                return this;
            },
            /**
             * Zjistí, zda element má CSS třídu.
             *
             * @param {String} className CSS třída
             * @returns {Boolean}
             */
            hasClass: function (className) {
                return this.getRegexp(className).test(this.element.className);
            },
            /**
             * Toggluje styly v argumentu.
             *
             * @param {Object} obj Objekt s nastavením stylů
             * @returns {Element}
             */
            toggleStyles: function (obj) {
                var old = this.element.debugOldStyles || {}, key;
                for (key in obj) {
                    if (core_hasOwnProperty.call(obj, key)) {
                        if (old[key] === undefined || (old[key] !== obj[key] && this.element.style[key] !== obj[key])) {
                            old[key] = this.element.style[key];
                            this.element.style[key] = obj[key];
                        } else {
                            this.element.style[key] = old[key];
                            old[key] = undefined;
                        }
                    }
                }
                this.element.debugOldStyles = old;
                return this;
            }
        };

        /**
         * Alias prototypu.
         * @var Element
         */
        Element.fn = Element.prototype;
        Element.fn.init.prototype = Element.fn;

        /**
         * Vytvoří DocumentFragment z HTML kódu.
         *
         * @param {String} code HTML kód
         */
        Element.create = function (code) {
            var fragment = document.createDocumentFragment(),
                body = document.createElement("body");
            body.innerHTML = code;
            while (body.firstChild) {
                fragment.appendChild(body.firstChild);
            }
            return fragment;
        };

        /**
         * Vrátí vždy novou instanci DocumentFragmentu.
         *
         * @returns {DocumentFragment}
         */
        Element.getFragment = function () {
            return document.createDocumentFragment();
        };

        return Element;
    }();

    cookie = (function () {
        /**
         * Cookie objekt.
         *
         * @name Cookie
         * @class
         * @inner
         */
        return {
            /**
             * Nastaví cookie dle názvu na kořen domény.
             *
             * @param {String} name Název
             * @param {String} value Hodnota
             * @param {Number} [days] Počet dní platnosti
             * @memberOf Cookie
             */
            set: function (name, value, days) {
                var expires = "", date;
                if (days) {
                    date = new Date();
                    date.setTime(date.getTime() + (days * 86400 * 1000));
                    expires = "; expires=" + date.toGMTString();
                }
                document.cookie = name + "=" + value + expires + "; path=/";
            },
            /**
             * Vrátí cookie dle názvu.
             *
             * @param {String} name Název
             * @returns {null|String}
             * @memberOf Cookie
             */
            get: function (name) {
                var nameEQ = name + "=", ca = document.cookie.split(';'), c, i, caLen;
                for (i = 0, caLen = ca.length; i < caLen; ++i) {
                    c = ca[i];
                    while (c.charAt(0) === ' ') {
                        c = c.substring(1, c.length);
                    }
                    if (c.indexOf(nameEQ) === 0) {
                        return c.substring(nameEQ.length, c.length);
                    }
                }
                return null;
            },
            /**
             * Smaže cookie dle názvu.
             *
             * @param {String} name Název
             * @memberOf Cookie
             */
            erase: function (name) {
                this.set(name, "", -1);
            }
        };
    }());

    filterer = function () {
        var Filterer = function () {
            return new Filterer.fn.init();
        };

        Filterer.prototype = {
            constructor: Filterer,
            /**
             * Kontejner pro filtry a prostředník mezi zprávou a jednotlivými filtry.
             *
             * @constructs
             */
            init: function () {
            },
            /**
             * Pole filtrů.
             *
             * @type {Object[]}
             * @private
             */
            filters: [],
            /**
             * Zaregistruje filtr zpráv.
             *
             * @param {Object} filter Filter
             * @returns {Filter}
             */
            registerFilter: function (filter) {
                this.filters.push(filter);
                return this;
            },
            /**
             * Přidá zprávu do všech filtrů.
             *
             * @param {Object} message Zpráva
             */
            addMessage: function (message) {
                core_each.call(this.filters, function (filter) {
                    filter.add(message);
                });
            },
            /**
             * Vykreslí všechny filtry.
             */
            render: function () {
                core_each.call(this.filters, function (filter) {
                    filter.render();
                });
            },
            /**
             * Ověří, zda jsou všechny filtry validní na zprávě v argumentech.
             *
             * @param {...Object} Argumenty
             * @return {Boolean}
             */
            valid: function () {
                var args = arguments;
                return core_every.call(this.filters, function (filter) {
                    return filter.valid.apply(filter, args);
                });
            },
            /**
             * Reset všech filtrů.
             */
            reset: function () {
                core_each.call(this.filters, function (filter) {
                    filter.reset();
                });
            }
        };

        /**
         * Alias prototypu.
         * @var Filterer
         */
        Filterer.fn = Filterer.prototype;
        Filterer.fn.init.prototype = Filterer.fn;

        return Filterer;
    }();

    templater = function () {
        var Templater = function (template) {
            return new Templater.fn.init(template);
        };

        Templater.prototype = {
            constructor: Templater,
            /**
             * @type {String} Šablona
             */
            template: null,
            /**
             * Vrátí RegExp pro nahrazení placeholderů v šabloně.
             *
             * @param {String} key Název placeholderu
             * @private
             * @function
             */
            getRegexp: (function () {
                var regexps = {};
                return function (key) {
                    regexps[key] = regexps[key] || new RegExp("{{" + key + "}}", "g");
                    return regexps[key];
                };
            }()),
            /**
             * Šablonovací systém.
             * Využívá placeholdery {{name}}.
             *
             * @param {String} template Šablona
             * @constructs
             */
            init: function (template) {
                this.template = template;
            },
            /**
             * Vyplní šablonu daty.
             *
             * @param {Object} data Mapa dat placeholder -> hodnota
             * @returns {Templater}
             */
            fill: function (data) {
                var key;
                for (key in data) {
                    if (core_hasOwnProperty.call(data, key)) {
                        this.template = this.template.replace(this.getRegexp(key), data[key]);
                    }
                }
                return this;
            },
            /**
             * Vytvoří z HTML šablony strom DOMu a vrátí jeho první uzel.
             *
             * @returns {{DOMElement}}
             */
            create: function () {
                return element.create(this.template).firstChild;
            }
        };

        /**
         * Alias prototypu.
         * @var Templater
         */
        Templater.fn = Templater.prototype;
        Templater.fn.init.prototype = Templater.fn;

        return Templater;
    }();

    message = function () {
        var Message = function (filterer, settings, template) {
            return new Message.fn.init(filterer, settings, template);
        };

        Message.prototype = {
            constructor: Message,
            /**
             * Uzel zprávy.
             *
             * @type {HtmlElement}
             */
            node: null,
            /**
             * Filterer.
             *
             * @type {Object}
             * @private
             */
            filterer: null,
            /**
             * Data z nastavení zprávy pro použití filtry.
             * Data jsou className a id.
             *
             * @type {Object}
             * @private
             */
            data: null,
            /**
             * Zpráva v Debugger.
             *
             * @constructs
             * @param {Object} filterer Filterer
             * @param {Object} settings Nastavení objektu
             * @param {String} settings.timestamp UNIX timestamp
             * @param {String} settings.timestampFormatted Formatted date and time
             * @param {String} settings.className Název CSS třídy
             * @param {String} settings.contentStyle Dodatečný styl obsahu
             * @param {String} settings.type Typ zprávy
             * @param {String} settings.page Hash stránky
             * @param {String} settings.id Id stránky
             * @param {String} settings.message Titulek zprávy
             * @param {String} settings.content Obsah zprávy
             * @param {String} template Šablona
             */
            init: function (filterer, settings, template) {
                var timestampParts = settings.timestamp.split('.');
                settings.className = settings.className || settings.classType;
                settings.timestampFormatted = new Date(timestampParts[0] * 1000).toLocaleTimeString() + '.' + timestampParts[1];
                settings.contentStyle = settings.contentStyle || '';
                template = template || templater("<div class='row border clearfix {{className}}'><div class='cell timestamp' title='{{timestamp}}'>{{timestampFormatted}}</div><div class='cell type' title='{{type}}'>{{type}}</div><div class='cell message'>{{message}}</div>" + "<div class='cell page'><a href='{{page}}' title='{{page}}'>#{{id}}</a></div><pre class='content'{{contentStyle}}>{{content}}</pre></div>");
                this.filterer = filterer;
                this.node = template.fill(settings).create();
                this.data = {
                    className: settings.className,
                    id: settings.id
                };
            },
            /**
             * Vrátí nastavení zprávy pro použití filtry.
             *
             * @returns {Object}
             */
            getSettings: function () {
                return this.data;
            },
            /**
             * Zobrazí/Skryje zprávu podle toho, zda projde aktuálním nastavením filtrů.
             */
            filter: function () {
                this.node.style.display = this.filterer.valid(this) ? 'block' : 'none';
            }
        };

        /**
         * Alias prototypu.
         * @var Message
         */
        Message.fn = Message.prototype;
        Message.fn.init.prototype = Message.fn;

        return Message;
    }();

    startMessage = function () {
        var parent = message.prototype, StartMessage;

        StartMessage = function (filterer, settings, template) {
            return new StartMessage.fn.init(filterer, settings, template);
        };

        StartMessage.prototype = core_create(parent);
        StartMessage.prototype.constructor = StartMessage;
        /**
         * Start zpráva Debuggeru.
         *
         * @name StartMessage
         * @augments Message
         * @constructs
         */
        StartMessage.prototype.init = function (filterer, settings, template) {
            var message, blocks, last = {};

            settings.message = core_map.call(settings.message,function (message) {
                return "<span class='global_variable clickable' title='Obsah " + message + "'>" + message + "</span>";
            }).join('');
            settings.content = core_map.call(settings.content,function (content) {
                return "<div class='debug_box debug_dumps hidden'>" + content + "</div>";
            }).join('');
            settings.contentStyle = settings.contentStyle || " style='display:block'";

            parent.init.call(this, filterer, settings, template);

            blocks = this.node.querySelectorAll('.debug_dumps');
            message = this.node.querySelector('.message');

            message.addEventListener('click', function (e) {
                var blockTitleNode = e.target,
                    index = core_indexOf.call(blockTitleNode.parentNode.childNodes, blockTitleNode),
                    blockContentNode = blocks[index],
                    lTitle;

                if (last.title) {
                    lTitle = last.title;
                    element(last.block).addClass('hidden');
                    element(lTitle).removeClass('active');
                    last = {};
                    if (lTitle === blockTitleNode) {
                        return;
                    }
                }

                element(blockContentNode).removeClass('hidden');
                element(blockTitleNode).addClass('active');
                last = {title: blockTitleNode, block: blockContentNode};
            });
            this.node.collapse = function () {
                if (last.title) {
                    element(last.block).addClass('hidden');
                    element(last.title).removeClass('active');
                    last = {};
                }
            };
        };

        /**
         * Alias prototypu.
         * @var StartMessage
         */
        StartMessage.fn = StartMessage.prototype;
        StartMessage.fn.init.prototype = StartMessage.fn;

        return StartMessage;
    }();

    toggableMessage = (function () {
        var parent = message.prototype, ToggableMessage;

        ToggableMessage = function (filterer, settings, template) {
            return new ToggableMessage.fn.init(filterer, settings, template);
        };

        ToggableMessage.prototype = core_create(parent);
        ToggableMessage.prototype.constructor = ToggableMessage;
        /**
         * Toggable zpráva Debuggeru.
         *
         * @name ToggableMessage
         * @augments Message
         * @constructs
         */
        ToggableMessage.prototype.init = function (filterer, settings, template) {
            var message, content;

            parent.init.call(this, filterer, settings, template);

            message = this.node.querySelector('.message');
            content = this.node.querySelector('.content');

            element(message).addClass('clickable');
            message.addEventListener('click', function () {
                element(content).toggleStyles({display: 'block'});
            });
            this.node.collapse = function () {
                content.style.display = "none";
            };
        };

        /**
         * Alias prototypu.
         * @var ToggableMessage
         */
        ToggableMessage.fn = ToggableMessage.prototype;
        ToggableMessage.fn.init.prototype = ToggableMessage.fn;

        return ToggableMessage;
    }());

    dumpMessage = (function () {
        var parent = toggableMessage.prototype, DumpMessage;

        DumpMessage = function (filterer, settings, template) {
            return new DumpMessage.fn.init(filterer, settings, template);
        };

        DumpMessage.prototype = core_create(parent);
        DumpMessage.prototype.constructor = DumpMessage;
        /**
         * DumpMessage zpráva Debuggeru.
         *
         * @name DumpMessage
         * @augments ToggableMessage
         * @constructs
         * @param {Object} settings.content.variable Proměnné
         * @param {Object} settings.content.callstack CallstackMessage
         */
        DumpMessage.prototype.init = function (filterer, settings, template) {
            var variables = core_map.call(settings.content.variables,function (variable) {
                return "<div class='debug_box debug_dumps'>" + variable + "</div>";
            }).join('');
            settings.content = variables + settings.content.callstack;

            parent.init.call(this, filterer, settings, template);
        };

        /**
         * Alias prototypu.
         * @var DumpMessage
         */
        DumpMessage.fn = DumpMessage.prototype;
        DumpMessage.fn.init.prototype = DumpMessage.fn;

        return DumpMessage;
    }());

    errorMessage = (function () {
        var parent = toggableMessage.prototype, ErrorMessage;

        ErrorMessage = function (filterer, settings, template) {
            return new ErrorMessage.fn.init(filterer, settings, template);
        };

        ErrorMessage.prototype = core_create(parent);
        ErrorMessage.prototype.constructor = ErrorMessage;
        /**
         * Error zpráva Debuggeru.
         *
         * @name ErrorMessage
         * @augments ToggableMessage
         * @constructs
         */
        ErrorMessage.prototype.init = function (filterer, settings, template) {
            var type = settings.type.toLowerCase();
            settings.className = settings.className || type.indexOf('exception') !== -1 ? 'exception' : type;
            parent.init.call(this, filterer, settings, template);
        };

        /**
         * Alias prototypu.
         * @var ErrorMessage
         */
        ErrorMessage.fn = ErrorMessage.prototype;
        ErrorMessage.fn.init.prototype = ErrorMessage.fn;

        return ErrorMessage;
    }());

    sqlQueryMessage = (function () {
        var parent = toggableMessage.prototype, SqlQueryMessage;

        SqlQueryMessage = function (filterer, settings, template) {
            return new SqlQueryMessage.fn.init(filterer, settings, template);
        };

        SqlQueryMessage.prototype = core_create(parent);
        SqlQueryMessage.prototype.constructor = SqlQueryMessage;
        /**
         * Vrátí nastavení obohacené o
         * queryType - Typ SQL dotazu
         * queryConnection - SQL spojení
         */
        SqlQueryMessage.prototype.getSettings = function () {
            var settings = parent.getSettings.apply(this), children = this.node.children;
            settings.queryType = children.item(2).textContent.split(/\s/, 2)[0].toUpperCase();
            settings.queryConnection = /\[([a-z0-9 #\-]+)\]/i.exec(children.item(1).textContent)[1];
            return settings;
        };
        /**
         * Zpráva o úspěšném SQL dotazem Debuggeru.
         *
         * @name SqlQueryMessage
         * @augments ToggableMessage
         * @constructs
         */
        SqlQueryMessage.prototype.init = function (filterer, settings, template) {
            parent.init.call(this, filterer, settings, template);
        };

        /**
         * Alias prototypu.
         * @var SqlQueryMessage
         */
        SqlQueryMessage.fn = SqlQueryMessage.prototype;
        SqlQueryMessage.fn.init.prototype = SqlQueryMessage.fn;

        return SqlQueryMessage;
    }());

    sqlErrorMessage = function () {
        var parent = toggableMessage.prototype, SqlErrorMessage;

        SqlErrorMessage = function (filterer, settings, template) {
            return new SqlErrorMessage.fn.init(filterer, settings, template);
        };

        SqlErrorMessage.prototype = core_create(parent);
        SqlErrorMessage.prototype.constructor = SqlErrorMessage;
        /**
         * Zpráva o neúspěšném SQL dotazem Debuggeru.
         *
         * @name SqlErrorMessage
         * @augments ToggableMessage
         * @constructs
         */
        SqlErrorMessage.prototype.init = function (filterer, settings, template) {
            parent.init.call(this, filterer, settings, template);
        };

        /**
         * Alias prototypu.
         * @var SqlErrorMessage
         */
        SqlErrorMessage.fn = SqlErrorMessage.prototype;
        SqlErrorMessage.fn.init.prototype = SqlErrorMessage.fn;

        return SqlErrorMessage;
    }();

    debug = function () {
        var
        // Uzly
            mainNode, filterNode, pagingNode, actionsNode,
        // Pole zpráv
            messageComponents = [],
        // Interní objekty
            MessageFactory, Debug, request, MessageTypeFilter, PageFilter, sqlConnectionFilter, SqlQueryFilter;

        request = (function () {
            var counter = 0, requests = {};
            /**
             * Správa a čítač požadavků.
             *
             * @name Request
             * @class
             * @inner
             */
            return {
                /**
                 * Vrací číslo requestu dle identifikátoru
                 *
                 * @param {String} id Id požadavku
                 * @returns {Number} Čítač
                 * @memberOf Request
                 */
                getCounterByRequestId: function (id) {
                    requests[id] = requests[id] || ++counter;
                    return requests[id];
                },
                /**
                 * Resetuje čítač.
                 *
                 * @memberOf Request
                 */
                reset: function () {
                    counter = 0;
                    requests = {};
                }
            };
        }());

        /**
         * Filtr dle typu zpráv.
         */
        MessageTypeFilter = (function () {
            var allType = 'all', pubsubTopic = 'filter/type', messageTypeFilterConstructor;
            messageTypeFilterConstructor = function (pubsub, node) {
                this.pubsub = pubsub;
                this.root = node;
                this.reset();
            };
            messageTypeFilterConstructor.prototype = {
                constructor: messageTypeFilterConstructor,
                add: function (message) {
                    var type = message.getSettings().className;
                    this.types[type] = this.types[type] || 0;
                    this.types[type]++;
                    this.types[allType]++;
                    this.pubsub.subscribe(pubsubTopic, utils.proxy(message.filter, message));
                },
                setActiveType: function (type) {
                    if (this.type === type) {
                        return;
                    }
                    element(this.spans[this.type]).removeClass('active');
                    element(this.spans[type]).addClass('active');
                    this.type = type;
                    this.pubsub.publish(pubsubTopic);
                },
                render: function () {
                    var fragment = element.getFragment(), spanFragment, key, clickHandle;
                    clickHandle = function (type) {
                        this.setActiveType(type);
                    };
                    for (key in this.types) {
                        if (core_hasOwnProperty.call(this.types, key)) {
                            spanFragment = element.create("<span class='filter" + (this.type === key ? " active" : "") + "' data='" + key + "'><span>" + key.toUpperCase() + " (" + this.types[key] + ")</span></span>");
                            spanFragment.firstChild.addEventListener('click', utils.proxy(clickHandle, this, key));
                            this.spans[key] = spanFragment.firstChild;
                            fragment.appendChild(spanFragment);
                        }
                    }

                    this.root.innerHTML = '';
                    this.root.appendChild(fragment);
                },
                valid: function (message) {
                    return this.type === allType || message.getSettings().className === this.type;
                },
                reset: function () {
                    this.type = allType;
                    this.types = {all: 0};
                    this.spans = {};
                    this.pubsub.unsubscribe(pubsubTopic);
                }
            };
            return messageTypeFilterConstructor;
        }());
        /**
         * Filtr dle požadavků.
         */
        PageFilter = (function () {
            var allPage = 'all', pubsubTopic = 'filter/page', pageFilterConstructor;
            pageFilterConstructor = function (pubsub, node) {
                this.pubsub = pubsub;
                this.root = node;
                this.reset();
            };
            pageFilterConstructor.prototype = {
                constructor: pageFilterConstructor,
                add: function (message) {
                    var page = message.getSettings().id;
                    this.pages[page] = this.pages[page] || '#' + page;
                    this.pubsub.subscribe(pubsubTopic, utils.proxy(message.filter, message));
                },
                setActivePage: function (page) {
                    if (this.page === page) {
                        return;
                    }
                    this.page = page;
                    this.pubsub.publish(pubsubTopic);
                },
                render: function () {
                    var html, key, selectFragment, handleChange;
                    handleChange = function (e) {
                        var el = e.target;
                        this.setActivePage(el.options[el.selectedIndex].value);
                    };
                    html = "<select>";
                    for (key in this.pages) {
                        if (core_hasOwnProperty.call(this.pages, key)) {
                            html += "<option value='" + key + "'" + (this.page ? " selected='selected'" : "") + ">"
                                + this.pages[key] + "</option>";
                        }
                    }
                    html += "</select>";
                    selectFragment = element.create(html);
                    selectFragment.firstChild.addEventListener('change', utils.proxy(handleChange, this));
                    this.root.innerHTML = "";
                    this.root.appendChild(selectFragment);
                },
                valid: function (message) {
                    return this.page === allPage || message.getSettings().id === +this.page;
                },
                reset: function () {
                    this.page = allPage;
                    this.pages = {all: "Vše"};
                    this.pubsub.unsubscribe(pubsubTopic);
                }
            };
            return pageFilterConstructor;
        }());
        /**
         * Filtr dle druhů SQL dotazů.
         */
        SqlQueryFilter = (function () {
            var pubsubTopic = 'filter/queryType', sqlQueryFilterConstructor;
            sqlQueryFilterConstructor = function (pubsub, node) {
                this.pubsub = pubsub;
                this.root = node;
                this.reset();
            };
            sqlQueryFilterConstructor.prototype = {
                constructor: sqlQueryFilterConstructor,
                add: function (message) {
                    var settings = message.getSettings(), query;
                    if (settings.className !== 'sql_query') {
                        return;
                    }

                    query = settings.queryType;
                    this.queries[query] = this.queries[query] || {count: 0, active: true};
                    this.queries[query].count++;
                    this.pubsub.subscribe(pubsubTopic, utils.proxy(message.filter, message));
                },
                setActiveQuery: function (query, checked) {
                    this.queries[query].active = checked;
                    this.pubsub.publish(pubsubTopic);
                },
                render: function () {
                    var node, inputFragment, labelFragment, fragment, wrapper, key, handleClick;
                    node = this.root.querySelector('.filter[data="sql_query"]');
                    if (!node) {
                        return;
                    }

                    handleClick = function (e) {
                        var el = e.target;
                        this.setActiveQuery(el.value, el.checked);
                    };
                    fragment = node.querySelector('.filter_sql_query') || element.create("<div class='filter_sql_query'></div>").firstChild;
                    wrapper = node.querySelector('.filter_sql_query_type') || element.create("<div class='inner_wrapper filter_sql_query_type'></div>").firstChild;
                    wrapper.innerHTML = "";
                    for (key in this.queries) {
                        if (core_hasOwnProperty.call(this.queries, key)) {
                            inputFragment = element.create("<input type='checkbox' id='sql_query_" + key + "' value='" + key + "'" + (this.queries[key].active ? " checked='checked'" : "") + " />");
                            inputFragment.firstChild.addEventListener('click', utils.proxy(handleClick, this));
                            labelFragment = element.create("<label for='sql_query_" + key + "'>" + key + " (<span>" + this.queries[key].count + "</span>)</label>");
                            wrapper.appendChild(inputFragment);
                            wrapper.appendChild(labelFragment);
                        }
                    }

                    fragment.appendChild(wrapper);
                    node.appendChild(fragment);
                },
                valid: function (message) {
                    var settings = message.getSettings();
                    return settings.className !== 'sql_query' || this.queries[settings.queryType].active;
                },
                reset: function () {
                    this.queries = {};
                    this.pubsub.unsubscribe(pubsubTopic);
                }
            };
            return sqlQueryFilterConstructor;
        }());

        sqlConnectionFilter = function () {
            var pubsubTopic = 'filter/queryConnection', SqlConnectionFilter;

            SqlConnectionFilter = function (pubsub, node) {
                return new SqlConnectionFilter.fn.init(pubsub, node);
            };

            SqlConnectionFilter.prototype = {
                constructor: SqlConnectionFilter,
                /**
                 * PubSub.
                 *
                 * @type {Object}
                 * @private
                 */
                pubsub: null,
                /**
                 * Hlavní uzel filtrů.
                 *
                 * @type {HtmlElement}
                 * @private
                 */
                root: null,
                /**
                 * Pole databázových spojení.
                 * Klíčem je název spojení.
                 * Hodnotou je objekt obsahující počet zpráv odpovídajících filtru a zda je filtr pro spojení aktivní.
                 *
                 * @type {Object[]}
                 * @private
                 */
                connections: null,
                /**
                 * Inicializace filtru pro databázová spojení.
                 *
                 * @param {Object} pubsub PubSub
                 * @param {HtmlElement} node Uzel
                 * @constructs
                 */
                init: function (pubsub, node) {
                    this.pubsub = pubsub;
                    this.root = node;
                    this.reset();
                },
                /**
                 * Přidá zprávu do filtru, pokud se jedná o úspěšný SQL dotaz.
                 * Zaregistruje do PubSub zpávu, aby byla informována o změnách ve filtru.
                 *
                 * @param {Object} message Zpráva.
                 */
                add: function (message) {
                    var settings = message.getSettings(), connection;
                    if (settings.className !== 'sql_query') {
                        return;
                    }

                    connection = settings.queryConnection;
                    this.connections[connection] = this.connections[connection] || {count: 0, active: true};
                    this.connections[connection].count++;
                    this.pubsub.subscribe(pubsubTopic, utils.proxy(message.filter, message));
                },
                /**
                 * Nastaví aktivní databázové spojení a informuje všechny přidružené zprávy.
                 *
                 * @param {String} connection Databázové spojení
                 * @param {Boolean} checked Zapnutý/Vypnutý
                 */
                setActiveConnection: function (connection, checked) {
                    this.connections[connection].active = checked;
                    this.pubsub.publish(pubsubTopic);
                },
                /**
                 * Vykreslí filtr.
                 */
                render: function () {
                    var node, inputFragment, labelFragment, fragment, wrapper, i = 0, key, handleClick;
                    node = this.root.querySelector('.filter[data="sql_query"]');
                    if (!node) {
                        return;
                    }

                    handleClick = function (e) {
                        var el = e.target;
                        this.setActiveConnection(el.value, el.checked);
                    };

                    fragment = node.querySelector('.filter_sql_query') || element.create("<div class='filter_sql_query'></div>").firstChild;
                    wrapper = node.querySelector('.filter_sql_query_connection') || element.create("<div class='inner_wrapper filter_sql_query_connection'></div>").firstChild;
                    wrapper.innerHTML = "";

                    for (key in this.connections) {
                        if (core_hasOwnProperty.call(this.connections, key)) {
                            inputFragment = element.create("<input type='checkbox' id='sql_connnection_" + i + "' value='" + key + "'" + (this.connections[key].active ? " checked='checked'" : "") + "/>");
                            inputFragment.firstChild.addEventListener('click', utils.proxy(handleClick, this));
                            labelFragment = element.create("<label for='sql_connnection_" + i + "'>" + key + " (<span>" + this.connections[key].count + "</span>)</label>");
                            wrapper.appendChild(inputFragment);
                            wrapper.appendChild(labelFragment);
                            i++;
                        }
                    }

                    fragment.appendChild(wrapper);
                    node.appendChild(fragment);
                },
                /**
                 * Je zpráva validní pro daný filtr?
                 *
                 * @param {Object} message Zpráva
                 * @returns {Boolean}
                 */
                valid: function (message) {
                    var settings = message.getSettings();
                    return settings.className !== 'sql_query' || this.connections[settings.queryConnection].active;
                },
                /**
                 * Resetuje filtr a odregistruje všechny zprávy z PubSub.
                 */
                reset: function () {
                    this.connections = {};
                    this.pubsub.unsubscribe(pubsubTopic);
                }
            };

            /**
             * Alias prototypu.
             * @var SqlConnectionFilter
             */
            SqlConnectionFilter.fn = SqlConnectionFilter.prototype;
            SqlConnectionFilter.fn.init.prototype = SqlConnectionFilter.fn;

            return SqlConnectionFilter;
        }();

        /**
         * Továrna pro zprávy, včetně hashmapy.
         *
         * @class
         */
        MessageFactory = {
            start: startMessage,
            timer: message,
            dump: dumpMessage,
            error: errorMessage,
            sql_query: sqlQueryMessage,
            sql_error: sqlErrorMessage,
            /**
             * Vytvoří zprávu z nastavení zprávy.
             *
             * @param {Object} filterer Filterer
             * @param {Object} settings Nastavení zprávy
             * @returns {Object} Objekt zprávy
             * @throws Error
             */
            create: function (filterer, settings) {
                var type = settings.classType;
                if (this[type] === undefined) {
                    throw new Error('Neznámý typ zprávy "' + type + '"');
                }
                settings.id = request.getCounterByRequestId(settings.id);
                return new this[type](filterer, settings);
            }
        };

        Debug = function (filterer, pubsub) {
            return new Debug.fn.init(filterer, pubsub);
        };

        Debug.prototype = {
            constructor: Debug,
            /**
             * Filterer.
             *
             * @type {Object}
             * @private
             */
            filterer: null,
            /**
             * PubSub.
             *
             * @type {Object}
             * @private
             */
            pubsub: null,
            /**
             * Vytvoří debug, získá důležité uzly, zaregistruje filtry.
             *
             * @param {Object} filterer Filterer
             * @param {Object} pubsub PubSub
             * @constructs
             */
            init: function (filterer, pubsub) {
                mainNode = document.getElementById('debug_toolbar_messages');
                filterNode = document.getElementById('debug_toolbar_filter');
                pagingNode = document.getElementById('debug_toolbar_paging');
                actionsNode = document.getElementById('debug_toolbar_actions');
                filterer
                    .registerFilter(new MessageTypeFilter(pubsub, filterNode))
                    .registerFilter(new PageFilter(pubsub, pagingNode))
                    .registerFilter(new SqlQueryFilter(pubsub, filterNode))
                    .registerFilter(new sqlConnectionFilter(pubsub, filterNode));
                this.filterer = filterer;
                this.pubsub = pubsub;
            },
            /**
             * Z pole znaků v podobě číslic vrátí string dat.
             *
             * @param {Number[]} input Pole znaků v pdoobě číslic
             * @param {Number} [chunkSize=65536] Velikost bufferu pro {@link String.fromCharCode}, max. je 65536
             * @returns {String}
             * @private
             */
            bufferedStringFromCharCode: function (input, chunkSize) {
                var len = input.length, k = 0, r = "";
                chunkSize = chunkSize || 65535;
                if (len < chunkSize) {
                    return String.fromCharCode.apply(null, input);
                }

                while (k < len) {
                    r += String.fromCharCode.apply(null, input.slice(k, k + chunkSize));
                    k += chunkSize;
                }
                return r;
            },
            /**
             * Dekomprimuje zprávy z Base64 a gzip do stringu.
             *
             * @param {String} compressed Komprimovaný string
             * @returns {String}
             * @private
             * @throws Error
             */
            decompress: function (compressed) {
                if (typeof Zlib.Inflate !== "function") {
                    throw new Error('Zlib modul nebyl načten. Debug nebude fungovat.');
                }
                if (typeof Base64Binary.decode !== "function") {
                    throw new Error('Base64Binary modul nebyl načten. Debug nebude fungovat.');
                }
                return this.bufferedStringFromCharCode(new Zlib.Inflate(Base64Binary.decode(compressed)).decompress());
            },
            /**
             * Název cookie, pod kterou je uloženo, zda se má Debug načíst otevřený nebo ne.
             */
            cookieDebugOpen: "debug_open",
            /**
             * Načte zprávy z hlaviček HTTP požadavku a překreslí debug.
             *
             * @param {Object} xhr XMLHttpRequest
             */
            loadFromXHR: function (xhr) {
                var debugCount = xhr.getResponseHeader('X-Debug-Count'), data = [], i;
                if (debugCount) {
                    for (i = 0; i < debugCount; ++i) {
                        data.push(xhr.getResponseHeader('X-Debug-' + i));
                    }
                    this.addMessages(data.join(''));
                    this.render();
                }
            },
            /**
             * Sestaví zprávu/y, přidá ji do filtereru.
             *
             * @param {String|Object} messages Komprimované zprávy ve stringu nebo objekt zprávy
             */
            addMessages: function (messages) {
                var filterer = this.filterer, messageComponent;
                if (!messages) {
                    return;
                }
                if (utils.isString(messages)) {
                    messages = JSON.parse(this.decompress(messages));
                }
                if (!utils.isArray(messages)) {
                    messages = [messages];
                }

                core_each.call(messages, function (message) {
                    messageComponent = MessageFactory.create(filterer, message);
                    messageComponents.push(messageComponent);
                    filterer.addMessage(messageComponent);
                });
            },
            /**
             * Vykreslí všechny zprávy a aplikuje filtry.
             */
            render: function () {
                var fragment = document.createDocumentFragment();
                core_each.call(messageComponents, function (message) {
                    fragment.insertBefore(message.node, fragment.firstChild);
                });
                mainNode.appendChild(fragment);
                this.filterer.render();
            },
            /**
             * Zobrazí/Skryje Debug buď dle aktuálního stavu nebo dle argumentu.
             *
             * @param {Boolean|undefined} [hide] Skrýt?
             * @private
             */
            toggle: function (hide) {
                var hidden = hide === undefined ? mainNode.style.display === "block" : hide;
                if (hidden) {
                    cookie.erase(this.cookieDebugOpen);
                } else {
                    cookie.set(this.cookieDebugOpen, "yes", null);
                }
                filterNode.style.display
                    = pagingNode.style.display
                    = actionsNode.style.display
                    = mainNode.style.display = hidden ? "none" : "block";
            },
            /**
             * Minimalizuje všechny zprávy.
             *
             * @private
             */
            collapseAll: function () {
                core_each.call(mainNode.children, function (child) {
                    if (typeof child.collapse === "function") {
                        child.collapse();
                    }
                });
            },
            /**
             * Otočí pořadí zpráv.
             *
             * @private
             */
            reverseOrder: function () {
                core_each.call(mainNode.children, function (child) {
                    mainNode.insertBefore(child, mainNode.firstChild);
                });
            },
            /**
             * Smaže všechny zprávy, vyčistí filterer a překreslí Debug.
             *
             * @private
             */
            deleteAll: function () {
                core_each.call(messageComponents, function (message) {
                    var node = message.node;
                    node.parentNode.removeChild(node);
                    message.node = null;
                });
                messageComponents = [];
                request.reset();
                this.filterer.reset();
                this.render();
            },
            /**
             * Skryje/Zobrazí argumenty přidružené k elementu dle aktuálního stavu.
             *
             * @param {HtmlElement} el Uzel
             * @private
             */
            toggleArguments: function (el) {
                element(el.parentNode.querySelector('.arguments')).toggleStyles({display: 'table'});
            }
        };

        /**
         * Alias prototypu.
         * @var Debug
         */
        Debug.fn = Debug.prototype;
        Debug.fn.init.prototype = Debug.fn;

        return Debug;
    }();

    // Inicializace z nastavení dle cookies
    debugInstance = debug(filterer(), pubsub());
    if (cookie.get(debugInstance.cookieDebugOpen) === "yes") {
        debugInstance.toggle(false);
    }

    window.Debugger = debugInstance;
}(window));
