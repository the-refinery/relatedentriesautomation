/**
 * relatedentriesautomation plugin for Craft CMS
 *
 * RelatedentriesautomationField Field JS
 *
 * @author    The Refinery
 * @copyright Copyright (c) 2018 The Refinery
 * @link      http://the-refinery.io/
 * @package   Relatedentriesautomation
 * @since     0.2.0RelatedentriesautomationRelatedentriesautomationField
 */

 ;(function ( $, window, document, undefined ) {

    var pluginName = "RelatedentriesautomationRelatedentriesautomationField",
        defaults = {
        };

    // Plugin constructor
    function Plugin( element, options ) {
        this.element = element;

        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.datamodel = false;
        this.modal = false;
        this.entryInfoModel = createEntryInfoModel();

        this.init();
    }

    Plugin.prototype = {

        init: function(id) {
            var _this = this;

            $(function () {
                /* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */
                var elId = _this.element.id; // fields-associateEntriesBy-field

                /* Create a knockout model for two-way binding of form values */

                _this.datamodel = createFieldModel(_this.options, _this.entryInfoModel);
                ko.applyBindings(_this.datamodel, document.getElementById(elId));

                /* create a modal for configuing queries */
                $('#' + elId + ' .open-modal-btn').on('click', function(){
                    if(_this.modal){
                        _this.modal.show();
                    }else{
                        _this.createModal();
                    }
                });
                console.log('entries vars', _this);

            });
        },

        createModal: function createModal(){
            // console.log(this);
            var values = this.options.values;
            // Setup modal HTML
            var $modal = $('<form id="' + this.element.id + '-modal" class="modal elementselectormodal smartmap-modal-address-search"/>').appendTo(Garnish.$bod),
                $body = $('<div class="body"/>').appendTo($modal),
                $footer = $('<footer class="footer"/>').appendTo($modal),
                $buttons = $('<div class="buttons right"/>').appendTo($footer),
                // $cancelBtn = $('<div class="btn modal-cancel">'+Craft.t('Cancel')+'</div>').appendTo($buttons);
                $saveBtn = $('<div class="btn submit">'+Craft.t('Close')+'</div>').appendTo($buttons);
            
            
            var self = this;
            /* load rest of HTML from template */
            $body.load(this.options.template, function(){
                // Initialize UI elements
                Craft.initUiElements($body);
                self.modal = new Garnish.Modal($modal);
                ko.applyBindings(self.datamodel, document.getElementById(self.element.id + '-modal'));
                self.modal.addListener($saveBtn, 'activate', self.modal.hide);

                /* load Sections/Entries list */
                var entryTypesUrl = Craft.getActionUrl('RelatedEntriesAutomation/Entriesinfo/ListAvailableEntryTypes');
                $.ajax({
                    method : 'GET',
                    url : entryTypesUrl
                })
                .done(function(data){
                    // console.log(data);
                    ko.utils.arrayPushAll(self.datamodel.entryInfo.entryTypes, data);
                });

                // search for Category fields and setup JS actions for each
            });
            
        },
    };

    function createFieldModel(options, entryInfoModel){
        // console.log('createFieldModel options', options);
        //var prefix = options.prefix.replace('-', '');
        var namspaceArr = options.namespace.split('-');
        var prefix = namspaceArr.shift();
        var namespace = prefix + '[' + namspaceArr.join('][') + ']';

        var values = options.values;
        var fieldOptions = options.fieldOptions;
        var model = {
            active: ko.observable((values.active === 1)), // force boolean
            limit: ko.observable(values.limit),
            order: ko.observable(values.order),
            orderDir: ko.observable(values.orderDir),
            selectedEntry: ko.observable(),
            entryTypes: ko.observableArray(generateSectionEntryArray(values.entryTypes)),
            orderingOptions: fieldOptions.orderingOptions,
            orderingDirections: fieldOptions.orderingDirections,
            entryInfo: entryInfoModel,
            entryTypeFieldName: 'fields[' + options.name + '][entryTypes][]'
        };
        model.addSelectedSection = function addSelectedSection(){
            // console.log('addSelectedSecion ', this.selectedEntry());
            this.entryTypes.push(createSectionEntryModel(this.selectedEntry()));
        };
        model.removeSelectedSection = function removeSelectedSection(entryType){
            // console.log('remove this thing', entryType);
            model.entryTypes.remove(entryType);
        };

        //  fields-customBlocks-13270-fields-similarPosts
        //  fields[customBlocks][13270][fields][similarPosts][orderDir]
        //  fieldscustomBlocks-13270-fields-[similarPosts][entryTypes][0][handle]
        //  fields[customBlocks][13270][fields][similarPosts][entryTypes][0][handle]
        //  fields-customBlocks-13270-fields-
        model.craftFieldName = function craftFieldName(){
            var args = (arguments.length === 1 ? [arguments[0]] : Array.apply(null, arguments));
            var fieldName = namespace + '[entryTypes][' + args[0] + ']';
            if(args.length > 1){
                fieldName += '[';
                fieldName += args.slice(1).join('][');
                fieldName += ']';
            }
            return fieldName;
        };
        model.orderLable = ko.computed(function orderLable(){
            var order = this.order();
            var optionObj = $.grep(this.orderingOptions, function(f){ return f.value == order; })[0];
            return optionObj.label;
        }, model);
        return model;
    }

    /**
     * Creates an empty model to hold entry info that will be loaded via
     * AJAX requests.
     * @return {[type]} [description]
     */
    function createEntryInfoModel(){
        var model = {
            entryTypes: ko.observableArray()
        };
        return model;
    }

    function createSectionEntryModel(entry){
        // console.log('createSectionEntryModel', entry);
        var params = entry.params || [];
        var handle = entry.handle;
        // if (typeof entry.handle === 'object') {
        //     handle = entry.handle.handle;
        // }
        var model = {
            handle: ko.observable(handle),
            name: ko.observable(entry.name),
            entryFields: ko.observableArray(),
            searchParams: ko.observableArray()
        };
        var entryTypesUrl = Craft.getActionUrl('RelatedEntriesAutomation/Entriesinfo/ListEntryFields', { typeHandle: handle });
        $.ajax({
            method : 'GET',
            url : entryTypesUrl
        })
        .done(function(data){
            // console.log('ListEntryFields', data);
            ko.utils.arrayPushAll(model.entryFields, data);
            model.searchParams(generateSearchParamsArray(params, model.entryFields));
            if(!model.searchParams().length){
                model.searchParams.push(createSearchParams(data[0], model.entryFields));
            }
        });
        model.addParam = function addParam(){
            model.searchParams.push(createSearchParams({}, model.entryFields));
        };
        model.removeParam = function removeParam(param){
            model.searchParams.remove(param);
        };
        
        return model;
    }

    function generateSectionEntryArray(entries){
        return entries.map(function(entry){
            return createSectionEntryModel(entry);
        });
    }

    function createSearchParams(data, fieldListFunction){
        var fieldList = fieldListFunction();
        // console.log('fieldList', fieldList);
        var selectedField = fieldList[0];
        if(data.handle){
            // find field handle in feildlist and set selectedField
            selectedField = $.grep(fieldList, function(f){ return f.handle == data.handle; })[0];
        }
        // console.log('createSearchParams', data, selectedField);
        var model = {
            selectedField: ko.observable(selectedField),
            operator: ko.observable(data.operator || false),
            value: ko.observable(data.value || null),
            availableCategories: ko.observableArray(),
            // selectedFieldEditable: ko.observable(data.selectedFieldEditable || false)
        };

        model.name = ko.pureComputed(function searchParamsName(){
            return this.selectedField().name;
        }, model);

        model.handle = ko.pureComputed(function searchParamsHandle(){
            //console.log('selectedField', this.selectedField());
            return this.selectedField().handle;
        }, model).extend({ notify: 'always' });

        model.type = ko.computed(function searchParamsType(){
            var type = this.selectedField().type;
            if (type === 'Categories') {
                loadCategories(this, this.selectedField().handle);
            }
            if (type === 'Entries') {
                loadEntries(this, this.selectedField().handle);
            }

            return type;
        }, model).extend({ notify: 'always' });

        model.availableOperators = ko.computed(function (){
            var availOps = paramOperators(this.selectedField().type);
            if(!this.operator() || !operatorInList(this.operator(), availOps)){
                this.operator(availOps[0].value);
            }
            // console.log('availableOperators', availOps);
            return availOps;
        }, model).extend({ notify: 'always' });

        model.operatorName = ko.computed(function operatorName(){
           var op = this.operator();
           // console.log('operatorName', op);
           var opObj = $.grep(this.availableOperators(), function(f){ return op == f.value; })[0];
           return opObj ? opObj.name : false;
        }, model);

        model.categoryTitle = ko.computed(function categoryTitle(){
            var self = this;
            // var catValue = this.value();
            // console.log('categoryTitle', this.value());
            if (this.type() !== 'Categories' && this.type() !== 'Entries') {
                return false;
            }
            // if(!Array.isArray(catValue)){
            //     catValue = [catValue];
            // }
            // var titles = catValue.map(function(value){
            //     return findCategoryTitle(value, self.availableCategories());
            // });
            // return titles.join(', ');
            return findCategoryTitle(this.value(), this.availableCategories());
        }, model);

        /**
         * Pevent user from changing the selected field once a model.value has been set.
         */
        model.selectedFieldEditable = ko.computed(function selectedFieldEditable(){
            return this.value() === null;
        }, model);

        function findCategoryTitle(value, categories){
            // console.log(value);
            var match = false;
            for (var i = 0; i < categories.length; i++) {
                // console.log('findCategoryTitle', categories[i].id());
                if(value == categories[i].id()){
                    match = categories[i].title();
                }
                if(!match && categories[i].descendants().length){
                    descendants = findCategoryTitle(value, categories[i].descendants());
                    match = descendants ? descendants : match;
                }
            }
            return match;
        }

        function operatorInList(operator, availOps){
            var inList = false;

            for (var i = 0; i < availOps.length; i++) {
                if(availOps[i].value == operator){
                    inList = true;
                }
            }

            return inList;
        }

        // if(!model.operator()){
        //     model.operator(model.availableOperators()[0].value);
        // }
        // model.craftFieldName = function craftFieldName(index, name){
        //     // return 'fields[' + options.name + '][' + name + '][]';
        //     return 'fields[filteredEntries][' + index + '][' + name + ']';
        // };
        return model;
    }

    function loadCategories(model, groupHandle){
        var categoryGroupUrl = Craft.getActionUrl('RelatedEntriesAutomation/Entriesinfo/ListCategories', { groupHandle: groupHandle });
        $.ajax({
            method : 'GET',
            url : categoryGroupUrl
        })
        .done(function(data){
            //console.log('loadCategories', data);
            model.availableCategories(generateRelatedItemsList(data));
        });
    }
    function loadEntries(model, fieldHandle){
        var categoryGroupUrl = Craft.getActionUrl('RelatedEntriesAutomation/Entriesinfo/ListAvailableEntries', { fieldHandle: fieldHandle });
        $.ajax({
            method : 'GET',
            url : categoryGroupUrl
        })
        .done(function(data){
            // console.log('loadEntries', data);
            model.availableCategories(generateRelatedEntryList(data));
        });
    }

    function paramOperators(type){
        var ops = [{
            value: 'LIKE', name: 'CONTAINS'
        }];
        switch(type){
            case 'Categories':
                ops = [{ value: 'RELATEDTO', name: 'IS'},
                { value: 'NOTRELATEDTO', name: 'IS NOT'}];
            break;
            case 'Entries':
                ops = [{ value: 'RELATEDTO', name: 'IS'}];
            break;
            case 'Date':
                ops = [{ value: '-', name: 'BEFORE'},
                { value: '+', name: 'AFTER'}];
            break;
            case 'Assets':
                ops = [{ value: 'ISSET', name: 'SET'},
                { value: 'UNSET', name: 'NOT SET'}];
                break;
        }
        return ops;
    }

    function generateSearchParamsArray(data, fieldList){
        return data.map(function(params){
            return createSearchParams(params, fieldList);
        });
    }

    function createRelatedItem(data){
        var model = {
            id: ko.observable(data.id),
            title: ko.observable(data.title),
            descendants: ko.observableArray()
        };
        if(data.descendants){
            model.descendants(generateRelatedItemsList(data.descendants));
        }
        return model;
    }

    function generateRelatedItemsList(items){
        return items.map(function(data){
            return createRelatedItem(data);
        });
    }

    function createRelatedEntry(data){
        // console.log('Section attributes', data);
        var model = {
            id: ko.observable(data.id),
            title: ko.observable(data.name),
            descendants: ko.observableArray()
        };
        if(data.entries){
            model.descendants(generateRelatedEntryList(data.entries));
        }
        return model;
    }

    function generateRelatedEntryList(items){
        return items.map(function(data){
            return createRelatedEntry(data);
        });
    }

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                new Plugin( this, options ));
            }
        });
    };

})( jQuery, window, document );
