<!doctype html>
<html lang="en" style="height: 100%; width: 100%;">
	<head>
		<meta charset="UTF-8">
		<title>Laravel PHP Framework</title>
		<style>
			@import url(//fonts.googleapis.com/css?family=Lato:700);
			body {
				margin:0;
				font-family:'Lato', sans-serif;
				text-align:center;
				color: #999;
			}
			.todo {
				width: 700px;
				height: 500px;
				position: absolute;
				left: 34%;
				top: 30%;
				margin-left: -100px;
				margin-top: -250px;
			}
			a, a:visited {
				text-decoration:none;
			}
			h1 {
				font-size: 32px;
				margin: 16px 0 0 0;
			}
		</style>
	</head>
	<body style   ="height: 100%; width: 100%;">
		<div class   ="todo">
			<form method='POST'>
				<h3>
				<br />
				<br />
				<input type="text" name='newtask' placeholder="Add New Task">
				<input type="submit" value="Add">
				</h3>
				<table style="height: 100%; width: 100%; border:2px solid black">
					<tr style  ="color:red; font-weight:bold;; text-decoration:underline;">
						<td>Task Name</td>
						<td>Complete</td>
						<td>Last Modified</td>
						<td>Actions</td>
					</tr>
					<?php foreach ($entries as $entry) :?>
						<? if ($entry['archived'] == false || $showArchives) :?>
						<tr>
							<td><?echo $entry['task']?></td>
							<td>
								<?if ($entry['completed'] == 0) : ?>
								<span>No</span>
								<? else : ?>
								<span>Yes</span>
								<? endif; ?>
							</td>
							<td><?echo date("D, M j - g:i:s A", strtotime($entry['updated_at']))?></td>
							<td>
								<table style="height: 100%; width: 100%;">
									<td style  ="text-align:left;">
										<a href="/?statuschange=<?php echo $entry['id'];?>&showArchives=<?php echo $showArchives;?>">
										<? if ($entry['completed'] == 0) : ?>
										<span>Mark Done</span>
										<? else : ?>
										<span>Mark Not Done</span>
										<? endif; ?>
										</a>
									</td>
									<td style="text-align:right;">
										<a href="/?archive=<?php echo $entry['id'];?>&showArchives=<?php echo $showArchives;?>">
										<?php if ($entry['archived'] == 0) : ?>
										<span>Archive</span>
										<? else : ?>
										<span>UnArchive</span>
										<? endif; ?>
										</a>
									</td>
								</table>
							</td>
						</tr>
						<?php endif;?>
					<?php endforeach;?>
				</table>
				
				<br />
				<br />
				
				<a href="/?showArchives=<?php echo !$showArchives;?>">
				<?php if ($showArchives) : ?>
				<span>Hide Archived Tasks</span>
				<? else : ?>
				<span>Show Archived Tasks</span>
				<? endif; ?>
				</a>
			</div>
		</body>
	</html>