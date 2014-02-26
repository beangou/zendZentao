--缺陷去除率
SELECT T1.product, T1.project, T1.assignedTo, T1.devbugs, T2.testbugs FROM (
SELECT t1.product, t1.project, t1.assignedTo, COUNT(*) AS devbugs FROM zt_bug t1
LEFT JOIN zt_testtask t2 ON (
	t2.product = t1.product 
	AND t2.project = t1.project 	
	)
WHERE t2.begin > t1.assignedDate
GROUP BY t1.product, t1.project, t1.assignedTo
ORDER BY t1.product, t1.project ) T1 LEFT JOIN (
SELECT t1.product, t1.project, COUNT(*) AS testbugs FROM zt_bug t1
LEFT JOIN zt_testtask t2 ON (
	t2.product = t1.product 
	AND t2.project = t1.project 	
	)
WHERE t2.begin <= t1.assignedDate
GROUP BY t1.product, t1.project, t1.assignedTo
ORDER BY t1.product, t1.project
) T2 ON (
	T1.product = T2.product
	AND T1.project = T2.project
) 
UNION
SELECT T1.product, T1.project, T1.assignedTo, T1.devbugs, T2.testbugs FROM (
SELECT t1.product, t1.project, t1.assignedTo, COUNT(*) AS devbugs FROM zt_bug t1
LEFT JOIN zt_testtask t2 ON (
	t2.product = t1.product 
	AND t2.project = t1.project 	
	)
WHERE t2.begin > t1.assignedDate
GROUP BY t1.product, t1.project, t1.assignedTo
ORDER BY t1.product, t1.project ) T1 RIGHT JOIN (
SELECT t1.product, t1.project, COUNT(*) AS testbugs FROM zt_bug t1
LEFT JOIN zt_testtask t2 ON (
	t2.product = t1.product 
	AND t2.project = t1.project 	
	)
WHERE t2.begin <= t1.assignedDate
GROUP BY t1.product, t1.project, t1.assignedTo
ORDER BY t1.product, t1.project
) T2 ON (
	T1.product = T2.product
	AND T1.project = T2.project
) 


--需求稳定度

SELECT T1.product, T1.project, T1.openedBy, T1.initstory, T2.addstory, T3.changestory FROM
(
SELECT t2.product, t1.project, t4.name, t2.openedBy, COUNT(t3.initstory_endtime) AS initstory
FROM zt_projectstory t1
LEFT JOIN zt_story t2 ON(t2.id=t1.story)
LEFT JOIN ict_initstory_endtime t3 ON (
	t3.project_id = t1.project
	AND t3.initstory_endtime >= t2.openedDate)
LEFT JOIN zt_project t4 ON(t4.id = t1.project)
GROUP BY project) T1 LEFT JOIN
(
SELECT t2.product, t1.project, t4.name, t2.openedBy, COUNT(t3.initstory_endtime) AS addstory
FROM zt_projectstory t1
LEFT JOIN zt_story t2 ON(t2.id=t1.story)
LEFT JOIN ict_initstory_endtime t3 ON (
	t3.project_id = t1.project
	AND t3.initstory_endtime <= t2.openedDate)
LEFT JOIN zt_project t4 ON(t4.id = t1.project)
GROUP BY project) T2 ON(T1.product=T2.product AND T1.project=T2.project AND T1.openedBy=T2.openedBy)
LEFT JOIN
(
SELECT t2.product, t1.project, t4.name, t2.openedBy, COUNT(t3.initstory_endtime) AS changestory
FROM zt_projectstory t1
LEFT JOIN zt_story t2 ON(t2.id=t1.story)
LEFT JOIN ict_initstory_endtime t3 ON (
	t3.project_id = t1.project
	AND t3.initstory_endtime >= t2.openedDate 
	AND t3.initstory_endtime < t2.lastEditedDate)
LEFT JOIN zt_project t4 ON(t4.id = t1.project)
GROUP BY project) T3 ON(T1.product=T3.product AND T1.project=T3.project AND T1.openedBy=T3.openedBy)
 
SELECT * FROM ict_stability;


--任务完成率
SELECT * FROM zt_task;
SELECT project, COUNT(*) AS alltasks FROM zt_task GROUP BY project;
SELECT project, COUNT(*) AS completedtasks FROM zt_task WHERE STATUS='closed' GROUP BY project;
SELECT * FROM zt_taskestimate;
SELECT * FROM zt_user;
SELECT * FROM zt_projectproduct;

--closed任务数
SELECT t3.product, t1.project, t1.assignedTo, COUNT(*) AS closedtasks FROM zt_task t1 
LEFT JOIN zt_project t2 ON (t2.id = t1.project)
LEFT JOIN zt_projectproduct t3 ON (t3.project = t1.project)
WHERE t1.status='closed'
GROUP BY t3.product, t1.project, t1.assignedTo;

--总共任务数
SELECT t3.product, t1.project, t1.assignedTo, COUNT(*) AS alltasks FROM zt_task t1 
LEFT JOIN zt_project t2 ON (t2.id = t1.project)
LEFT JOIN zt_projectproduct t3 ON (t3.project = t1.project)
GROUP BY t3.product, t1.project, t1.assignedTo;


--使用左连接和右连接

SELECT T1.product, T1.project, T1.assignedTo, T1.closedtasks, T2.alltasks FROM (
SELECT t3.product, t1.project, t1.assignedTo, COUNT(*) AS closedtasks FROM zt_task t1 
LEFT JOIN zt_project t2 ON (t2.id = t1.project)
LEFT JOIN zt_projectproduct t3 ON (t3.project = t1.project)
WHERE t1.status='closed'
GROUP BY t3.product, t1.project, t1.assignedTo) T1 LEFT JOIN (
SELECT t3.product, t1.project, t1.assignedTo, COUNT(*) AS alltasks FROM zt_task t1 
LEFT JOIN zt_project t2 ON (t2.id = t1.project)
LEFT JOIN zt_projectproduct t3 ON (t3.project = t1.project)
GROUP BY t3.product, t1.project, t1.assignedTo
) T2 ON (
	T1.product = T2.product
	AND T1.project = T2.project
)
UNION
SELECT T1.product, T1.project, T1.assignedTo, T2.closedtasks, T1.alltasks FROM (
SELECT t3.product, t1.project, t1.assignedTo, COUNT(*) AS alltasks FROM zt_task t1 
LEFT JOIN zt_project t2 ON (t2.id = t1.project)
LEFT JOIN zt_projectproduct t3 ON (t3.project = t1.project)
GROUP BY t3.product, t1.project, t1.assignedTo) T1 LEFT JOIN (
SELECT t3.product, t1.project, t1.assignedTo, COUNT(*) AS closedtasks FROM zt_task t1 
LEFT JOIN zt_project t2 ON (t2.id = t1.project)
LEFT JOIN zt_projectproduct t3 ON (t3.project = t1.project)
WHERE t1.status='closed'
GROUP BY t3.product, t1.project, t1.assignedTo
) T2 ON (
	T1.product = T2.product
	AND T1.project = T2.project	
)

SELECT * FROM ict_completed;