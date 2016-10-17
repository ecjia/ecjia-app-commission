<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 后台权限API
 * @author royalwang
 *
 */
class orders_admin_purview_api extends Component_Event_Api {
    
    public function call(&$options) {
        $purviews = array(
//             array('action_name' => __('编辑发货状态'), 'action_code' => 'order_ss_edit', 'relevance'   => ''),
        );
        
        return $purviews;
    }
}

// end