codeValidator = {
	validator: "pattern",
	param: "[A-Za-z]*",
	message: function() {
		return "Kód smí obsahovat jen písmena bez diakritiky";
	}
};

codeValidatorReq = {
	validator: "pattern",
	param: "[A-Za-z]+",
	message: function() {
		return "Kód nesmí být prázdný a může obsahovat jen písmena bez diakritiky";
	}
};

integerValidator = {
	validator: "pattern",
	param: "[0-9]*",
	message: function() {
		return "Zadejte celé číslo";
	}
};

coordValidator = {
	validator: "pattern",
	param: "[0-9]*\\.?[0-9]*",
	message: function() {
		return "Zadejte desetinné číslo s desetinnou tečkou";
	}
};


ajaxErrorHandler = function(jqXHR) {
	var resp = JSON.parse(jqXHR.responseText);
	switch (jqXHR.status) {
		case 400: alert('Neznámá funkce: ' + resp.error); break;
		case 401: window.location.redirect('?page=login'); break;
		case 403: alert('Nedostatečná práva'); break;
		case 422:
			if ("code" in resp) {
				switch (resp.code) {
					case 1451: alert('Pokoušíte se smazat řádek, na nějž se odkazuje řádek v jiné tabulce.'); break;
					case 1062: alert('Kódy musí být v rámci hry unikátní.'); break;
					default: alert('Databázová chyba:\n' + resp.error); break;
				}
			} else {
				alert("Chyba na straně serveru:\n" + resp.error);
			}
			break;
		default: alert('Chyba na straně serveru:\n' + resp.error); break;
	}
};

function setGrid(cfg) {
	defaultCfg = {
		autoload: true,
		inserting: true,
		editing: true,
		confirmDeleting: false,
		deleteConfirm: "Opravdu?",
		controller: {
			loadData: function(filter) {
				var url = "../api/" + cfg.table;
				if ("pageIndex" in filter && "pageSize" in filter) {
					url += "?page=" + filter.pageIndex + "&pageSize=" + filter.pageSize;
				}
				return $.ajax({
					type: "GET",
					url: url,
					error: ajaxErrorHandler
				});
			},
			insertItem: function(item) {
				return $.ajax({
					type: "POST",
					url: "../api/" + cfg.table,
					data: item,
					error: ajaxErrorHandler
				});
			},
			updateItem: function(item) {
				return $.ajax({
					type: "PUT",
					url: "../api/" + cfg.table,
					data: item,
					error: ajaxErrorHandler
				});
			},
			deleteItem: function(item) {
				return $.ajax({
					type: "DELETE",
					url: "../api/" + cfg.table,
					data: item,
					error: ajaxErrorHandler
				});
			}
		},
	};
	$("#grid").jsGrid($.extend(defaultCfg, cfg));
}


var DatetimeField = function(config) {
	var defaultConfig = {
		dayOfWeekStart: 1,
		yearStart: 2000,
		yearEnd: 2040
	};

	this.config = $.extend({}, defaultConfig, config);
	this.formatter = new DateFormatter();

    jsGrid.Field.call(this, config);
};

DatetimeField.prototype = new jsGrid.Field({
	outputFormat: "Y/m/d H:i",
	
	format: function(date) {
		return this.formatter.formatDate(date, this.outputFormat);
	},

   	itemTemplate: function(value) {
		return value == null || value == "" ? "" : this.format(new Date(value));
    },

    insertTemplate: function(value) {
		return this._insertPicker = $("<input>").datetimepicker(this.config);
    },

    editTemplate: function(value) {
		this.config["value"] = value;
        return this._editPicker = $("<input>").datetimepicker(this.config);
    },

    insertValue: function() {
		if (this._insertPicker.datetimepicker("data", "changed")) {
			value = this._insertPicker.datetimepicker("getValue");
			return this.format(value);
		} else {
			return null;
		}
    },

    editValue: function() {
		value = this._editPicker.datetimepicker("getValue");
		return this.format(value);
    }
});

jsGrid.fields.datetime = DatetimeField;


var MultiSelectField = function(config) {
	var defaultConfig = {
		options: [],
		multiple: true,
		autocomplete: true,
		icon: "times",
	};

	var items = config.items;
	delete config.items;

	this.config = $.extend({}, defaultConfig, config);
	this.optionMap = {};
	
	for (item of items) {
		this.optionMap[item[config.valueField]] = item[config.textField];
		this.config.options.push({
			value: item[config.valueField],
			label: item[config.textField]
		});
	};

	jsGrid.Field.call(this, config);

};

MultiSelectField.prototype = new jsGrid.Field({

	itemTemplate: function(values) {
		if (values == null) return "";
		ret = [];
		for (val of values) {
			ret.push(this.optionMap[val]);
		}
		return ret.join(", ");
	},

	insertTemplate: function() {
		var span = document.createElement("span");
		this.insertControl = new SelectPure(span, this.config);
		return span;
	},

	editTemplate: function(value) {
		var span = document.createElement("span");
		this.editControl = new SelectPure(span, {
			...this.config,
			value: value
		});
		return span;
	},

	insertValue: function() {
		return this.insertControl.value();
	},

	editValue: function() {
		return this.editControl.value();
	}
});

jsGrid.fields.multiselect = MultiSelectField;
