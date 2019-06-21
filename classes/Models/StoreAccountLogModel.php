<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 19/6/21 021
 * Time: 13:19
 */

namespace Ecjia\App\Commission\Models;

use Royalcms\Component\Database\Eloquent\Model;

class StoreAccountLogModel extends Model
{
    protected $table = 'store_account_log';

    protected $primaryKey = 'log_id';

    /**
     * 可以被批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;
}