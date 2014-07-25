
;(function($) {
	
	var methods = {
			
			clearErrors: function($form)
			{
				$form.find('.errors').each(function() {
					$(this).html('');
					$(this).parent().removeClass('input-error');
				});
			},
			
			displayErrors: function($form, errors, prefix)
			{
				$.each(errors, function(idx, error) {
					var $errorsDiv = $form.find('#' + prefix + idx + '-errors'); 
					if ($errorsDiv.length) {
						var html = '<ul class="error">'
						$.each(error, function(i, err) {
							html += '<li>' + err + '</li>';
						});
						html += '</ul>';
						$errorsDiv.html(html);
						$errorsDiv.parent().addClass('input-error');
					} else {
						methods.displayErrors($form, error, idx + '-');
					}
				});
			}
	};
	
	var handlers = {
		
		onSubmit: function(e, extraData) {
			var $form = $(e.currentTarget);
			var data  = $form.serializeArray();
			if (extraData) {
				$.each(extraData, function(idx, value) {
					data.push({
						name: idx,
						value: value
					});
				});
			}
			
			var dataType = $form.data('type');
			if (!dataType) dataType = 'json';
			
			$.ajax({
				url: $form.attr('action'),
				type: $form.attr('method'),
				dataType: dataType,
				data: data
			})
			.done(function(data, textStatus, jqXHR) {
				methods.clearErrors($form);
				if (!data.valid) {
					methods.displayErrors($form, data.errors);
				}
				$form.trigger('yk.forms.done', {data: data, status:textStatus, jqXHR:jqXHR});
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				$form.trigger('yk.forms.fail', {jqXHR: jqXHR, status: textStatus, error: errorThrown});
			});
			return false;
		},
		
		onChange: function(e) {
			
			var $element = $(e.currentTarget);
			var validate = $element.data('validate');
			var data = {};
			if (validate) {
				data.validationGroup = validate;
			}
			
			$element.parents('form').trigger('submit', data);
			return false;
		}
	};
	
	$.fn.form = function () 
	{
		return this.each(function() {
			var $form = $(this);
			$form.submit(handlers.onSubmit);
			$form.find('[data-trigger="submit"]').change(handlers.onChange);
		});
	}

	$(function() {
		$('form:not([data-handle-by]), form[data-handle-by="yk-form"]').form();
	});
	
})(jQuery);
