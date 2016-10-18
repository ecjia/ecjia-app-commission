// JavaScript Document
;(function (app, $) {
    app.order = {
        init: function () {
            app.order.searchForm();
        },
 
        searchForm : function () {
			/*$(".start_date,.end_date").datepicker({
				format: "yyyy-mm"
			});*/
			$('.screen-btn').on('click', function(e) {
				e.preventDefault();
				//var start_date		= $("input[name='start_date']").val(); 		//开始时间
				//var end_date		= $("input[name='end_date']").val(); 		//结束时间
				var order_sn		= $("input[name='order_sn']").val();
				var url				= $("form[name='searchForm']").attr('action'); //请求链接
				//if(start_date       == 'undefind')start_date='';
				//if(end_date       	== 'undefind')end_date='';
				if(order_sn        	== 'undefind')order_sn='';
				if(url        		== 'undefind')url='';

				/*if (start_date == '') {
					var data = {
							message : "查询的开始时间不能为空！",
							state : "error",
					};
					ecjia.admin.showmessage(data);
					return false;
				} else if(end_date == '') {
					var data = {
							message : "查询的结束时间不能为空！",
							state : "error",
					};
					ecjia.admin.showmessage(data);
					return false;
				};
				
				if (start_date >= end_date && (start_date != '' && end_date !='')) {
					var data = {
							message : "查询的开始时间不能大于结束时间！",
							state : "error",
					};
					ecjia.admin.showmessage(data);
					return false;
				}*/
				var parmars = '';
				if (order_sn) {
					parmars += '&order_sn=' + order_sn;
				}
				
				ecjia.pjax(url + parmars/*'&start_date=' + start_date + '&end_date=' +end_date*/);
				
			});
		}
 
    }
 
 
})(ecjia.admin, jQuery);
 
// end