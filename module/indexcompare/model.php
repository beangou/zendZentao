<?php
/**
 * The model file of index module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2013 青岛易软天创网络科技有限公司 (QingDao Nature Easy Soft Network Technology Co,LTD www.cnezsoft.com)
 * @license     LGPL (http://www.gnu.org/licenses/lgpl.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     index
 * @version     $Id: model.php 4129 2013-01-18 01:58:14Z wwccss $
 */
?>
<?php
class indexcompareModel extends model
{	
	/**
	 * Get tasks of a project.
	 *
	 * @param  int    $projectID
	 * @param  string $status       all|needConfirm|wait|doing|done|cancel
	 * @param  string $type
	 * @param  object $pager
	 * @access public
	 * @return array
	 */
	public function getIndex($proname='', $empname='')
	{
// 		$orderBy = str_replace('status', 'statusCustom', $orderBy);
// 		$orderBy = str_replace('left', '`left`', $orderBy);
// 		$type    = strtolower($type);
// 		$tasks = $this->dao->select('t1.*, t2.id AS storyID, t2.title AS storyTitle, t2.version AS latestStoryVersion, t2.status AS storyStatus, t3.realname AS assignedToRealName')
// 		->from(TABLE_TASK)->alias('t1')
// 		->leftJoin(TABLE_STORY)->alias('t2')->on('t1.story = t2.id')
// 		->leftJoin(TABLE_USER)->alias('t3')->on('t1.assignedTo = t3.account')
// 		->where('t1.project')->eq((int)$projectID)
// 		->andWhere('t1.deleted')->eq(0)
// 		->beginIF($type == 'undone')->andWhere("(t1.status = 'wait' or t1.status ='doing')")->fi()
// 		->beginIF($type == 'needconfirm')->andWhere('t2.version > t1.storyVersion')->andWhere("t2.status = 'active'")->fi()
// 		->beginIF($type == 'assignedtome')->andWhere('t1.assignedTo')->eq($this->app->user->account)->fi()
// 		->beginIF($type == 'finishedbyme')->andWhere('t1.finishedby')->eq($this->app->user->account)->fi()
// 		->beginIF($type == 'delayed')->andWhere('deadline')->between('1970-1-1', helper::now())->andWhere('t1.status')->in('wait,doing')->fi()
// 		->beginIF(strpos(',all,undone,needconfirm,assignedtome,delayed,finishedbyme,', ",$type,") === false)->andWhere('t1.status')->in($type)->fi()
// 		->orderBy($orderBy)
// 		->page($pager)
// 		->fetchAll();
// 		$this->loadModel('common')->saveQueryCondition($this->dao->get(), 'task', $type == 'needconfirm' ? false : true);
// 		if($tasks) return $this->processTasks($tasks);

		//获取评价指标
		//1.从任务完成率开始
		
		return $this->dao->select('*')->from(TABLE_TASK)->where('id')->eq((int)$taskID)->fetchAll();
		
// 		return array();
	}
	
	public function getInitStoryEndTime() {
		//SELECT id FROM zt_project WHERE id NOT IN(SELECT project_id FROM ict_initstory_endtime);
		$recordedIds = indexcompare::dealForDbIn($this->dao->select('project_id')->from(TABLE_ICTINITSTORY_ENDTIME)->fetchAll());
		$ids = $this->dao->select('t1.id, t1.name')->from(TABLE_PROJECT)->alias('t1')->where('t1.id')->notin($recordedIds)->fi()->fetchAll();
// 		$ids = array('' => '') + $ids;
		return $ids;
	}
	
	public function insertTime($id='', $endTime='') {
		$data->project_id   = $id;
		$data->initstory_endtime     = $endTime;
		$this->dao->insert(TABLE_ICTINITSTORY_ENDTIME)->data($data)->exec();
	}
	
	public function selectProAndTime() {
// 		SELECT t4.`name`, t2.`name`, t1.`initstory_endtime` FROM ict_initstory_endtime t1
// LEFT JOIN zt_project t2 ON (t1.`project_id` = t2.`id`)
// LEFT JOIN zt_projectproduct t3 ON (t2.`id` = t3.`project`)
// LEFT JOIN zt_product t4 ON (t3.`product` = t4.id);

		return $this->dao->select('t4.name as prodName, t2.name as projName, t1.initstory_endtime')->from(TABLE_ICTINITSTORY_ENDTIME)->alias('t1')
		->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project_id = t2.id')
		->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t2.id = t3.project')
		->leftJoin(TABLE_PRODUCT)->alias('t4')->on('t3.product = t4.id')
		->fetchAll();
	}
	
	//查询项目需求稳定度
	public function selectStability() {
		//原始需求
		$initStory = $this->dao->select('t2.product, t5.name as productname, t1.project, t4.name as projectname, COUNT(t3.initstory_endtime) AS initstory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.initstory_endtime >= t2.openedDate AND t3.initstory_endtime >= t2.lastEditedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->leftJoin(TABLE_PRODUCT)->alias('t5')->on('t5.id = t2.product')->groupBy('project')
		->fetchAll();
		$initLen = count($initStory);
		
		//新增需求
		$addStory = $this->dao->select('t2.product, t1.project, t4.name, COUNT(t3.initstory_endtime) AS addstory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.project_id = t1.project AND t3.initstory_endtime <= t2.openedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')->groupBy('project')
		->fetchAll();
		$addLen = count($addStory);
		
		//修改需求
		$changeStory = $this->dao->select('t2.product, t1.project, t4.name, COUNT(t3.initstory_endtime) AS changestory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.initstory_endtime >= t2.openedDate AND t3.initstory_endtime < t2.lastEditedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')->groupBy('project')
		->fetchAll();
		$changeLen = count($changeStory);
		
		//将上述三个结果合成
// 		$result = $this->dao->select('T1.product, T1.project, T1.name, T1.initstory, T2.addstory, T3.changestory')
// 		->from($initStory->alias('T1'))->leftJoin($addStory->alias('T2'))->on('T1.project = T2.project')
// 		->leftJoin($changeStory->alias('T3'))->on('T1.project = T3.project')
// 		->fetchAll();

		for ($i=0; $i<$initLen; $i++) {
			$initStory[$i]->addstory = $addStory[$i]->addstory;
			$initStory[$i]->changestory = $changeStory[$i]->changestory;
			if ($initStory[$i]->initstory != 0) {
				$initStory[$i]->stability = (($addStory[$i]->addstory + $changeStory[$i]->changestory)*100 / $initStory[$i]->initstory). '%';
			}
		}

		return $initStory;
		
		/*
		$initSQL = 'SELECT t2.product, t1.project, t4.name, COUNT(t3.initstory_endtime) AS initstory
		FROM zt_projectstory t1
		LEFT JOIN zt_story t2 ON(t2.id=t1.story)
		LEFT JOIN ict_initstory_endtime t3 ON (
		t3.project_id = t1.project
		AND t3.initstory_endtime >= t2.openedDate
		AND t3.initstory_endtime >= t2.lastEditedDate)
		LEFT JOIN zt_project t4 ON(t4.id = t1.project)
		GROUP BY project';
		
		$addSQL = 'SELECT t1.project, t4.name, COUNT(t3.initstory_endtime) AS addstory
		FROM zt_projectstory t1
		LEFT JOIN zt_story t2 ON(t2.id=t1.story)
		LEFT JOIN ict_initstory_endtime t3 ON (
		t3.project_id = t1.project
		AND t3.initstory_endtime <= t2.openedDate)
		LEFT JOIN zt_project t4 ON(t4.id = t1.project)
		GROUP BY project';
		
		$changeSQL = 'SELECT t1.project, t4.name, COUNT(t3.initstory_endtime) AS changestory
		FROM zt_projectstory t1
		LEFT JOIN zt_story t2 ON(t2.id=t1.story)
		LEFT JOIN ict_initstory_endtime t3 ON (
			t3.project_id = t1.project
			AND t3.initstory_endtime >= t2.openedDate 
			AND t3.initstory_endtime < t2.lastEditedDate)
		LEFT JOIN zt_project t4 ON(t4.id = t1.project)
		GROUP BY project';
		
		$resultSQL = 'SELECT T1.product, T1.project, T1.name, T1.initstory, T2.addstory, T3.changestory FROM ('
				. $initSQL
				. ') T1 LEFT JOIN ('
				. $addSQL
				. ') T2 ON(T1.project=T2.project) LEFT JOIN ('
				. $changeSQL
				. ') T3 ON(T1.project=T3.project)'; 
		*/

	}
}
