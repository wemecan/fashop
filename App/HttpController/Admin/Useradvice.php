<?php

namespace App\HttpController\Admin;
/**
 * 意见反馈
 */
class Useradvice extends Admin
{
	public function _initialize()
	{
		parent::_initialize();
	}

	/**
	 * 意见反馈列表
	 * @author 孙泉
	 */
	// public function index(){
	//     $user_advice_model = model('UserAdvice');
	//     $table_prefix = \EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL.prefix');
	//     $condition = array();
	//     $get = $this->get;
	//     if($get['advice_type_id']) $condition['advice_type_id'] = $get['advice_type_id'];
	//     //分页
	//     $count      = \App\Model\UserAdvice::init()->where($condition)->count();
	//     $Page        = new \App\extend\Page($count,$get['rows'] ? $get['rows'] : 20);
	//     $this->page     = $Page->show();
	//     $page       = $Page->currentPage.','.$Page->listRows;
	//     $field = '*,(select title from '.$table_prefix.'user_advice_type where id=advice_type_id) as type_title';
	//     $order = 'create_time asc';
	//     $list = \App\Model\UserAdvice::getUserAdviceList($condition, $field, $order, $page);
	//     $this->assign('list',$list);

	//     $type_list = model('UserAdviceType')->getUserAdviceTypeList(array(), '*', 'id asc', '1,20');
	//     $this->assign('type_list',$type_list);
	//     return $this->send();
	// }

	/**
	 * 这个是自己关联查询用户的
	 * @return
	 */
	public function index()
	{
		$condition = [];
		$get       = $this->get;
		if( $get['type_id'] ){
			$condition['type.id'] = $get['type_id'];
		}
		//分页
		$count = \App\Model\UserAdvice::init()->alias( 'user_advice' )->join( 'user_advice_type type', 'user_advice.advice_type_id = type.id' )->where( $condition )->count();
		$field = 'user_advice.*,user.username,type.title';
		$order = 'user_advice.create_time desc';
		$list  = \App\Model\UserAdvice::init()->getUserAdviceMoreList( $condition, $field, $order, $this->getPageLimit() );

		return $this->send( Code::success, [
			'total_number' => $count,
			'list'         => $list,
		] );
	}

	/**
	 * 意见反馈详情
	 * @author 孙泉
	 */
	public function detail()
	{
		$get                         = $this->get;
		$condition['user_advice.id'] = $get['id'];
		if( !$condition ){
			exit( $this->send( '参数错误' ) );
		}

		$row = \App\Model\UserAdvice::init()->getUserAdviceMoreInfo( $condition, '*' );

		$result         = [];
		$result['info'] = $row;
		return $this->send( Code::success, $result );
	}

	/**
	 * 意见反馈删除
	 * @author 孙泉
	 */
	public function del()
	{
		$post = $this->post;
		$ids  = $post['ids'];
		$res  = db( 'UserAdvice' )->where( [
				'id' => is_array( $ids ) ? ['in', implode( ',', $ids )] : $ids,
			] )->delete();
		if( $res ){
			//记录行为
			action_log( 'update_user_advice', 'user_advice', $ids, $this->user['id'] );
			return $this->send( Code::success );
		} else{
			return $this->send( Code::error );
		}
	}
}

?>