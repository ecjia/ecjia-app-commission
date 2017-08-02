// JavaScript Document
;(function (app, $) {
    app.bill_list = {
        init: function () {
            app.bill_list.searchForm();
        },
 
        searchForm : function () {
			$('.screen-btn').on('click', function(e) {
				e.preventDefault();
				var keywords = $("input[name='keywords']").val();
				var merchant_keywords = $("input[name='merchant_keywords']").val();
				var url = $("form[name='searchForm']").attr('action'); //请求链接
				
				if (keywords == 'undefind') keywords = '';
				if (merchant_keywords == 'undefind') merchant_keywords = '';
				if (url == 'undefind') url = '';

				var parmars = '';
				if (keywords) {
					parmars += '&keywords=' + keywords;
				}
				if (merchant_keywords) {
					parmars += '&merchant_keywords=' + merchant_keywords;
				}
				ecjia.pjax(url + parmars);
			});
		},
		searchFormDay : function () {
			$(".date").datepicker({
				format: "yyyy-mm-dd",
			});
			
			$('.screen-btn').on('click', function(e) {
				e.preventDefault();
				var start_date = $("input[name='start_date']").val();
				var end_date = $("input[name='end_date']").val();
				var merchant_keywords = $("input[name='merchant_keywords']").val();
				var url = $("form[name='searchForm']").attr('action'); //请求链接
				
				if (start_date == 'undefind') start_date = '';
				if (end_date == 'undefind') end_date = '';
				if (merchant_keywords == 'undefind') merchant_keywords = '';
				if (url == 'undefind') url = '';

				var parmars = '';
				if (start_date) {
					parmars += '&start_date=' + start_date;
				}
				if (end_date) {
					parmars += '&end_date=' + end_date;
				}
				if (merchant_keywords) {
					parmars += '&merchant_keywords=' + merchant_keywords;
				}
				ecjia.pjax(url + parmars);
			});
		},
    }
})(ecjia.admin, jQuery);
 
// end