function GCList(field) {
	this.field = field;
    this.dialogId = 'list_dialog';
    this.options = {};
	this.urls = {
		'ajax/dataList.php': ['data'],
		'ajax/lookupList.php': ['lookup_table'],
		'ajax/fieldList.php': ['class_text','label_angle','label_color','label_outlinecolor','label_size','label_font','label_priority','angle','color','outlinecolor','size','labelitem','labelsizeitem','classitem','classtitle','qtfield_name','data_field_1','data_field_2','data_field_3','table_field_1','table_field_2','table_field_3','filter_field_name'],
		'ajax/dbList.php': ['field_format','table_name','symbol_ttf_name','symbol_name','symbol_user_pixmap'],
		'ajax/fontList.php': ['symbol_user_font']
	};
	this.requireSquareBrackets = ['class_text','label_angle','label_color','label_outlinecolor','label_size','label_font','label_priority','angle','color','outlinecolor','size','classtitle'];
	this.listData = {};
	this.selectedData = {};
	this.currentStep = null;
	this.totSteps = null;
	this.loadList = function(params) {
		var self = this;
        var dialogId = this.dialogId;
        var options = this.options;
		var dialogElement = $('#'+dialogId);
        var resultTable = dialogElement.find('table');

		self.listData = {};
		
		$.extend(self.selectedData, params);
		params.selectedField = self.field;
		
		var requestUrl = null;
		$.each(self.urls, function(url, fields) {
			if($.inArray(self.field, fields) > -1) {
				requestUrl = url;
				return false;
			}
		});
		if(requestUrl === null) {
			alert('Not implemented');
			return;
		}
		
		$.ajax({
			url: requestUrl,
			type: 'POST',
			dataType: 'json',
			data: params,
			success: function(response) {
                var errorMsg = null;
                if(typeof response !== 'object') {
                    errorMsg = 'response is not in JSON format';
                } else if (response === null) {
                    errorMsg = 'response is null';                    
                } else if (typeof response.result === 'undefined' ||
                        response.result !== 'ok') {
                    errorMsg = 'invalid result field';                    
                } else if (
                        typeof response.fields !== 'object' ||
                        typeof response.data !== 'object' ||
                        typeof response.step === 'undefined' ||
                        typeof response.steps === 'undefined') {
                    errorMsg = 'invalid server response format';
                } else if (typeof response.error !== 'undefined') {
                    if ($.inArray(response.error, ['catalog_id','layertype_id','data']) > -1) {
                        errorMsg = 'invalid ' . response.error;
                    } else {
                        errorMsg = response.error;
                    }
                }
                if (errorMsg !== null) {
                    alert('Error');
                    dialogElement.dialog('close');
                    return;
                }
                resultTable.empty();
				
				self.currentStep = response.step;
				self.totSteps = response.steps;
				
                // create table header
				var html = '<tr>';
				$.each(response.fields, function(fieldName, fieldTitle) {
					html += '<th>'+fieldTitle+'</th>';
				});
				html += '</tr>';
				
                // add rows with symbols to table
				$.each(response.data, function(rowId, rowData) {
					html += '<tr data-row_id='+rowId+'>';
					$.each(response.fields, function(fieldName, foo) {
						if(typeof rowData[fieldName] === 'undefined' || rowData[fieldName] === null) {
							html += '<td class="data-'+fieldName+'"></td>';
							return;
						}
						html += '<td class="data-'+fieldName+'">'+rowData[fieldName]+'</td>';
					});
					html += '</tr>';
				});
				resultTable.append(html);
				
				$.each(response.data_objects, function(rowId, rowData) {
					self.listData[rowId] = rowData;
				});
				
				resultTable.find('td').hover(function() {
					$(this).css('cursor', 'pointer');
				},function() {
					$(this).css('cursor', 'default');
				});
                
                if (typeof options.handle_click === 'undefined' || options.handle_click) {
                    resultTable.find('td').click(function(event) {
                        var rowId = $(this).parent().attr('data-row_id');
                        $.extend(self.selectedData, self.listData[rowId]);

                        if(self.currentStep == self.totSteps || typeof(self.listData[rowId].is_final_step) != 'undefined' && self.listData[rowId].is_final_step == 1) {
                            $.each(self.selectedData, function(key, val) {
                                if($.inArray(key, self.requireSquareBrackets) > -1) val = '['+val+']';
                                $('#'+key).val(val);
                            });
                            dialogElement.dialog('close');
                        } else {
                            self.currentStep += 1;
                            self.selectedData.step = self.currentStep;
                            self.loadList(self.selectedData);
                        }
                    });
                };
                if (typeof options.events !== 'undefined' && typeof options.events.list_loaded !== 'undefined') {
                    options.events.list_loaded();
                }
                        
			},
			error: function() {
				alert('AJAX request returned with error');
			}
		});
	};
};

function openList(txt_field, data) {
    var selectedField;
	if (txt_field.indexOf('.') > 0){
		var tmp = txt_field.split('.');
		selectedField = tmp[0];
	} else {
		selectedField = txt_field;
	}
	
	if(!$.isArray(data)) {
		if (dat.length>0) data = data.split('@');
		else data = new Array();
	}
	
	var params = {};
	$.each(data, function(e, field) {
		if($('#'+field).length > 0 && $('#'+field).val()) {
			params[field] = $('#'+field).val();
		}
	});
	
	$('#list_dialog').dialog({
		width:500,
		height:350,
		title:'',
		open: function() {
			var list = new GCList(selectedField);
			list.loadList(params);
		}
	});
}