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
		return $ids;
	}
	
	public function insertTime($id='', $endTime='') {
		$data->project_id   = $id;
		$data->initstory_endtime     = $endTime;
		$this->dao->insert(TABLE_ICTINITSTORY_ENDTIME)->data($data)->exec();
	}
	
	public function selectProAndTime() {

		return $this->dao->select('t4.name as prodName, t2.name as projName, t1.initstory_endtime')->from(TABLE_ICTINITSTORY_ENDTIME)->alias('t1')
		->leftJoin(TABLE_PROJECT)->alias('t2')->on('t1.project_id = t2.id')
		->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t2.id = t3.project')
		->leftJoin(TABLE_PRODUCT)->alias('t4')->on('t3.product = t4.id')
		->fetchAll();
	}
	
	//查询项目需求稳定度
	public function selectStability($productArr = array()) {
		//原始需求
		$initStory = $this->dao->select('t2.product, t5.name as productname, t1.project, t4.name as projectname, COUNT(t3.initstory_endtime) AS initstory, 0 AS addstory, 0 AS changestory, \'0%\' AS stability')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.initstory_endtime >= t2.openedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->leftJoin(TABLE_PRODUCT)->alias('t5')->on('t5.id = t2.product')
		->where('t2.product')->in($productArr)
		->groupBy('t1.project')->orderBy('t2.product, t1.project')
		->fetchAll();
		$initLen = count($initStory);
		
		// 		->andWhere('t3.initstory_endtime IS NOT NULL')
		//新增需求
		$addStory = $this->dao->select('t2.product, t1.project, t4.name, 0 AS initstory, COUNT(t3.initstory_endtime) AS addstory, 0 AS changestory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.project_id = t1.project AND t3.initstory_endtime <= t2.openedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->where('t2.product')->in($productArr)
// 		->andWhere('t3.initstory_endtime IS NOT NULL')
		->groupBy('t1.project')->orderBy('t2.product, t1.project')
		->fetchAll();
		$addLen = count($addStory);
		
		//修改需求
		$changeStory = $this->dao->select('t2.product, t1.project, t4.name, 0 AS initstory, 0 AS addstory, COUNT(t3.initstory_endtime) AS changestory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.initstory_endtime >= t2.openedDate AND t3.initstory_endtime < t2.lastEditedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->where('t2.product')->in($productArr)
// 		->andWhere('t3.initstory_endtime IS NOT NULL')
		->groupBy('t1.project')->orderBy('t2.product, t1.project')
		->fetchAll();
		$changeLen = count($changeStory);
		
		for ($i=0; $i<$initLen; $i++) {
// 			for ()
			$initStory[$i]->addstory = $addStory[$i]->addstory;
			$initStory[$i]->changestory = $changeStory[$i]->changestory;
			if ($initStory[$i]->initstory != 0) {
				$initStory[$i]->stability = 100*(round(($addStory[$i]->addstory + $changeStory[$i]->changestory) / $initStory[$i]->initstory, 4)). '%';
			} else if ($addStory[$i]->addstory + $changeStory[$i]->changestory > 0) {
				$initStory[$i]->stability = '无穷大';
			}
			
			//去除没用的记录（即原始需求数/新增需求数均为0）
			if ($initStory[$i]->initstory == 0 && $initStory[$i]->addstory == 0) {
				array_splice($initStory, $i, 1);
			}
		}
		
		$newResult = $this->dao->select('T1.product, T2.name AS productname, T3.name AS projectname, SUM(T1.initstory) AS initstory, SUM(T1.addstory) AS addstory,
				SUM(T1.changestory) AS changestory')
						->from(TABLE_ICTSTABILITY)->alias('T1')
						->leftJoin(TABLE_PRODUCT)->alias('T2')->on('T2.id = T1.product')
						->leftJoin(TABLE_PROJECT)->alias('T3')->on('T3.id = T1.project')
						->where('T1.product')->in($productArr)
						->andWhere('T1.openedBy')->ne('')
						->groupBy('T1.product, T1.project')
						->orderBy('T1.product, T1.project')
						->fetchAll();

		return indexcompare::staDealArrForRowspan($newResult, 'product');
	}
	
	//查询个人需求稳定度
	public function selectPerStability($productArr = array()) {
		//原始需求
		$initStory = $this->dao->select('t1.project, t4.name, t2.title, t2.openedBy, COUNT(t3.initstory_endtime) AS initstory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.initstory_endtime >= t2.openedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->leftJoin(TABLE_PRODUCT)->alias('t5')->on('t5.id = t2.product')
		->where('t2.product')->in($productArr)
		->groupBy('t1.project, t2.openedBy')->orderBy('t2.product, t1.project')
		->fetchAll();
		$initLen = count($initStory);
	
		//新增需求
		$addStory = $this->dao->select('t1.project, t4.name, t2.title, t2.openedBy, COUNT(t3.initstory_endtime) AS addstory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.project_id = t1.project AND t3.initstory_endtime <= t2.openedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->where('t2.product')->in($productArr)
		->groupBy('t1.project, t2.openedBy')->orderBy('t2.product, t1.project')
		->fetchAll();
		$addLen = count($addStory);
	
		//修改需求
		$changeStory = $this->dao->select('t1.project, t4.name,t2.title, t2.openedBy, COUNT(t3.initstory_endtime) AS changestory')->from(TABLE_PROJECTSTORY)->alias('t1')
		->leftJoin(TABLE_STORY)->alias('t2')->on('t2.id=t1.story')
		->leftJoin(TABLE_ICTINITSTORY_ENDTIME)->alias('t3')
		->on('t3.project_id = t1.project AND t3.initstory_endtime >= t2.openedDate AND t3.initstory_endtime < t2.lastEditedDate')
		->leftJoin(TABLE_PROJECT)->alias('t4')->on('t4.id = t1.project')
		->where('t2.product')->in($productArr)
		->groupBy('t1.project, t2.openedBy')->orderBy('t2.product, t1.project')
		->fetchAll();
		$changeLen = count($changeStory);
	
		for ($i=0; $i<$initLen; $i++) {
			$initStory[$i]->addstory = $addStory[$i]->addstory;
			$initStory[$i]->changestory = $changeStory[$i]->changestory;
			if ($initStory[$i]->initstory != 0) {
				$initStory[$i]->stability = 100*round(($addStory[$i]->addstory + $changeStory[$i]->changestory) / $initStory[$i]->initstory, 4). '%';
			} else if ($addStory[$i]->addstory + $changeStory[$i]->changestory > 0) {
				$initStory[$i]->stability = '无穷大';
			}
			//去除没用的记录（即原始需求数/新增需求数均为0）
			if ($initStory[$i]->initstory == 0 && $initStory[$i]->addstory == 0) {
				array_splice($initStory, $i, 1);
			} 
		}
		
		
		$newResult = $this->dao->select('T1.project, T3.name AS projectname, T1.openedBy, T1.initstory, T1.addstory,
				T1.changestory, T1.stability')
						->from(TABLE_ICTSTABILITY)->alias('T1')
						->leftJoin(TABLE_PRODUCT)->alias('T2')->on('T2.id = T1.product')
						->leftJoin(TABLE_PROJECT)->alias('T3')->on('T3.id = T1.project')
						->where('T1.product')->in($productArr)
						->andWhere('T1.openedBy')->ne('')
						->groupBy('T1.product, T1.project, T1.openedBy')
						->orderBy('T1.product, T1.project')
						->fetchAll();
	
		return indexcompare::dealArrForRowspan($newResult, 'project');
	}
	
	//项目任务完成率
	public function selectCompleted($productArr = array()) {
		$allTasks = $this->dao->select('t3.product, t4.name as productname, t1.project, t2.name as projectname,  COUNT(*) AS alltasks')->from(TABLE_TASK)->alias('t1')
		->leftJoin(TABLE_PROJECT)->alias('t2')->on('t2.id = t1.project')
		->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t3.project = t1.project')
		->leftJoin(TABLE_PRODUCT)->alias('t4')->on('t4.id = t3.product')
		->where('t3.product')->in($productArr)
		->groupBy('t1.project')->fetchAll();
		$closedTasks = $this->dao->select('project, COUNT(*) AS closedtasks')->from(TABLE_TASK)
		->where('status')->eq('closed')
		->groupBy('project')
		->orderBy('project')
		->fetchAll();
		$allLen = count($allTasks);
		$closedLen = count($closedTasks);
		
		for ($i=0; $i<$allLen; $i++) {
			for ($j=0; $j<$closedLen; $j++) {
				if ($closedTasks[$j]->project == $allTasks[$i]->project && $closedTasks[$j]->closedtasks > 0) {
					$allTasks[$i]->closedtasks = $closedTasks[$j]->closedtasks;
					break;
				} else {
					$allTasks[$i]->closedtasks = 0;
				}
			}
			$allTasks[$i]->completed = 100*round(($allTasks[$i]->closedtasks / $allTasks[$i]->alltasks), 4). '%';
		}
		
		$newResult = $this->dao->select('T1.product, T2.name AS productname, T3.name AS projectname, SUM(T1.closedtasks) AS closedtasks, 
				SUM(T1.alltasks) AS alltasks, SUM(T1.closedtasks)/SUM(T1.alltasks) AS completed')
						->from(TABLE_ICTCOMPLETED)->alias('T1')
						->leftJoin(TABLE_PRODUCT)->alias('T2')->on('T2.id = T1.product')
						->leftJoin(TABLE_PROJECT)->alias('T3')->on('T3.id = T1.project')
						->where('T1.product')->in($productArr)
						->andWhere('T1.assignedTo')->ne('')
						->groupBy('T1.product, T1.project')
						->orderBy('T1.product, T1.project')
						->fetchAll();
		
		return indexcompare::dealArrForRowspan($newResult, 'product');
	}
	
	//个人任务完成率
	public function selectPerCompleted($productArr = array()) {
		$allTasks = $this->dao->select('t1.project, t2.name, t1.assignedTo, COUNT(*) AS alltasks')->from(TABLE_TASK)->alias('t1')
		->leftJoin(TABLE_PROJECT)->alias('t2')->on('t2.id = t1.project')
		->leftJoin(TABLE_PROJECTPRODUCT)->alias('t3')->on('t3.project = t2.id')
		->where('t3.product')->in($productArr)
		->groupBy('t1.project, t1.assignedTo')
		->orderBy('t3.product, t1.project')
		->fetchAll();
		$closedTasks = $this->dao->select('project, assignedTo, COUNT(*) AS closedtasks')->from(TABLE_TASK)
		->where('status')->eq('closed')->groupBy('project, assignedTo')->fetchAll();
		$allLen = count($allTasks);
		$closedLen = count($closedTasks);
	
		for ($i=0; $i<$allLen; $i++) {
			for ($j=0; $j<$closedLen; $j++) {
				if ($closedTasks[$j]->project == $allTasks[$i]->project &&
					$closedTasks[$j]->assignedTo == $allTasks[$i]->assignedTo &&
					$closedTasks[$j]->closedtasks > 0) {
					$allTasks[$i]->closedtasks = $closedTasks[$j]->closedtasks;
					break;
				} else {
					$allTasks[$i]->closedtasks = 0;
				}
			}
			$allTasks[$i]->completed = 100*round($allTasks[$i]->closedtasks / $allTasks[$i]->alltasks, 4). '%';
		}
		
		
		$newResult = $this->dao->select('T1.project, T2.name AS projectname, T1.assignedTo, T1.closedtasks, T1.alltasks, 
 						T1.closedtasks/ T1.alltasks AS completed')
						->from(TABLE_ICTCOMPLETED)->alias('T1')
						->leftJoin(TABLE_PROJECT)->alias('T2')->on('T2.id = T1.project')
						->where('T1.product')->in($productArr)
						->andWhere('T1.assignedTo')->ne('')
						->groupBy('T1.product, T2.id, T1.assignedTo')
						->orderBy('T1.product, T2.id')
						->fetchAll();
	
		return indexcompare::dealArrForRowspan($newResult, 'project');
	}
	
}
