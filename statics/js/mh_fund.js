// JavaScript Document
;
(function(app, $) {
	app.fund = {
		init : function() {
			app.fund.set_value();
			app.fund.subForm();
		},
		set_value: function() {
			$('.set_value').off('click').on('click', function(e) {
				e.preventDefault();
				var $this = $(this),
					val = $this.attr('data-money');
				$("input[name='money']").val(val);
			})
		},
		
		subForm : function () {
			var $form = $("form[name='fundForm']");
			var option = {
				rules: {
					money: {
                        required: true
                    },
                    desc: {
                        required: true
                    }
                },
                messages: {
                	money: {
                        required: '提现金额不能为空'
                    },
                    desc: {
                        required: '备注内容不能为空'
                    }
                },
				submitHandler : function() {
					$form.ajaxSubmit({
						dataType : "json",
						success : function(data) {
							ecjia.merchant.showmessage(data);
						}
					});
				}
			}
			var options = $.extend(ecjia.merchant.defaultOptions.validate, option);
			$form.validate(options);
		},
	};
})(ecjia.merchant, jQuery);

// end