<?php
//import CommonConfig & CommonFunction
require_once('config.php');
require_once('functions.php');
require_once('cconsole.php');

//Connect DB
$dbh=connectDb();

//Create TaskArray
$tasks=array();

//Create SQLStatement(CheckSeqMax)
$seq=0;
$sql="select max(seq)+1 from tasks where type !='deleted'";
$seq=$dbh->query($sql)->fetchColumn();
if (is_null($seq)){
	$seq=0;
}

//Create SQLStatement(ShowAllTasks)
$sql="select * from tasks where type != 'deleted' order by plan";

foreach($dbh->query($sql) as $row){
	array_push($tasks,$row);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<title>Todo_App</title>
	<meta charset="utf-8">
   	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="css/style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/footerFixed.js"></script>
</head>
<body>			
	<!--Header Start-->
	<div class="container">
		<div id="header" class="bg-primary">Header</div>
	</div>
	<!--Header End-->

	<!--Contents Start-->		
	<div class="container">
		<!--Side Start-->		
		<div id="sidemenu" class="col-md-3">
			<ul class="nav nav-pills nav-stacked navbar-inverse">
				<li><a href="#">Link1</a></li>
				<li><a href="#">Link2</a></li>
				<li><a href="#">Link3</a></li>
				<li><a href="#">Link4</a></li>
			</ul>
		</div>	
		<!--Side End-->		

		<!--Main Start-->		
		<div id="contents" class="col-md-9">
			<br />
			<label class="control-label" for="inputSuccess1">Add NewTask</label>
			<input type="text" id="title" />
			<input type="date" id="plan" />
			<input type="button" class="btn btn-primary btn-xs addTask" value="追加">
			<br />
			<br />
			<table class="table table-condensed table-striped" id="tasks">
				<thead>
					<th>Check</th>
					<th>Title</th>
					<th>Date</th>
					<th>Delete</th>
					<th>Drag</th>
				</thead>
				<tbody>
				<?php foreach ($tasks as $task) : ?>
					<tr id="task_<?php echo h($task['id']); ?>" data-id="<?php echo h($task['id']); ?>">
						<td class="col-sm-1"><input type="checkbox" class="checkTask" <?php if($task['type']=="done"){ echo "checked";} ?>></td>
						<td class="col-sm-6 title <?php echo h($task['type']); ?>"><?php echo h($task['title']); ?></td>
						<td class="col-sm-3 plan <?php echo h($task['type']); ?>"><?php echo h($task['plan']); ?></td>
						<td class="col-sm-1 deleteTask"><input type="button" class="btn btn-danger btn-xs" value="Delete"></td>
						<td class="col-sm-1 dragTask">[並替]</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<!--Main End-->		
	</div>
	<!--Contents End-->		

	<!--Footer Start-->		
	<div class="container">
		<div id="footer" class="bg-primary">footer</div>
	</div>
	<!--Footer End-->		

	<script>
	$(function(){
		//Focus AddTaskInput
		$('#title').focus();

		//Add Task
		$(".addTask").click(function(){
			var title=$('#title').val();
			var plan=$('#plan').val();
			//jquery.post(url,data,callback)
			$.post('_ajax_add_task.php',{
				title:title,
				plan:plan
			},function(rs){
				var e=$(
					'<tr id="task_'+rs+'" data-id="'+rs+'">'+
					'<td class="col-sm-1"><input type="checkbox" class="checkTask"></td>'+
					'<td class="col-sm-6 title"></td>'+
					'<td class="col-sm-2"></td>'+
					'<td class="col-sm-1 deleteTask"><input type="button" class="btn btn-danger btn-xs" value="Delete"></td>'+
					'<td class="col-sm-1 dragTask">[並替]</td>'+
					'</tr>'
				);
				$('#tasks')
					.append(e)
					.find('tr:last td:eq(1)')
					.text(title)
					.next()
					.text(plan);
				console.log('dataidは',$('#tasks').find('tr:last').data('id'))
				$('#title')
					.text('')
					.focus();
			});
		});

		//Delete Task
		$(document).on('click','.deleteTask',function(){
			if (confirm('本当に削除しますか？')){
				var id=$(this).parent().data('id');
				$.post('_ajax_delete_task.php',{
					id:id
				},function(rs){
					$('#task_'+id).fadeOut(150);
				});
			}
		});

		//Check Task
		$(document).on('click','.checkTask',function(){
			var id=$(this).parent().data('id');
			$.post('_ajax_check_task.php',{
				id:id
			},function(rs){
				if($(this).hasClass('done')){
					$(this).removeClass('done').addClass('title').addClass('plan');
				} else{
					$(this).addClass('done').next().removeClass('title').removeClass('plan');;
				}
			});
		});

		//Edit taskTitle
		$('.title').click(function(){
			if(!$(this).hasClass('on')){				
				$(this).addClass('on');
				var id=$(this).parent().data('id');
				var title=$(this).text();
				$(this).html('<input type="text" id="updateTask" value="'+title+'" />');
				$('.title > input').focus().on("change",function(){
					var inputTitle=$(this).val();
					if(inputTitle===''){
						inputTitle = this.defaultValue;
					};
					$(this).parent().removeClass('on').text(inputTitle);
					$.post('_ajax_update_title.php',{
						id:id,
						title:inputTitle
					},function(){
					});
				});
			};
		});

		//Edit taskPlan
		$('.plan').click(function(){
			if(!$(this).hasClass('on')){				
				$(this).addClass('on');
				var id=$(this).parent().data('id');
				var plan=$(this).text();
				$(this).html('<input type="date" id="updateTask" value="'+plan+'" />');
				$('.plan > input').focus().on("change",function(){
					var inputPlan=$(this).val();
					if(inputPlan===''){
						inputPlan = this.defaultValue;
					};
					$(this).parent().removeClass('on').text(inputPlan);
					$.post('_ajax_update_plan.php',{
						id:id,
						plan:inputPlan
					},function(){
					});
				});
			};
		});

		//Sort Task
		/*
		$("#tasks").sortable({
			axis:'y',
			opacity:0.2,
			handle:'.dragTask',
			update:function(){
				$.post('_ajax_sort_task.php',{
					task:$(this).sortable('serialize')
				});
			}
		});
		*/
	});
	</script>
</body>
</html>