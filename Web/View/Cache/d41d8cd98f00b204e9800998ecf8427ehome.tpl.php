<!DOCTYPE HTML>
<html>
	<head>
		<title>首页 - 风铃论坛</title>
		<link type="text/css" rel="stylesheet" href="/default.css" />
		<meta charset="UTF-8" />
		<meta name="Description" content="这是一个绿色，免费，自由的论坛，来这里和大家一起交流吧！" />
		<meta name="Keyword" content="Web,Js,Css,HTML,PHP,C,Java,C++,Linux,Windows"  />
		<meta name="viewport" content="" />
		<script src="/default.js"></script>
	</head>
<body>
<header>
	<div id="logo">风铃论坛</div>
	<nav>
		<ul>
			<li> <a href="#">首页</a> </li>
			<li> <a href="#">论坛</a> </li>
		</ul>
	</nav>
</header>

<section>
	<aside class="left">
		<figure>
			<figcaption>最新动态</figcaption>
			<ul>
				<li> <a href="#"> 1.论坛刚刚开启。 </a> </li>
				<li> <a href="#"> 2. </a> </li>
				<li> <a href="#"> 3. </a> </li>
			</ul>
		</figure>
	</aside>

	<aside class="right">
		<figure>
			<figcaption> 登录面板 </figcaption>
			<form action="?">
				<table border="0" align="center" cellspacing="1px">
					<tr>
						<td> 账号：</td>
						<td> <input id="user" size="11" /> </td>
					</tr>

					<tr>
						<td colspan="2"></td>
					</tr>

					<tr>
						<td> 密码：</td>
						<td> <input type="password" id="password" size="11" /> </td>
					</tr>

					<tr>
						<td colspan="2"> </td>
					</tr>

					<tr>
						<td colspan="2"> <button id="submit" type="submit">登录</button> </td>
					</tr>

				</table>
			</form>
		</figure>
	</aside>

	<article>
		<figure>
			<figcaption> 最新帖子</figcaption>
			<ul>
				<li> <a href="#"> 1.网站建设网页设计完全实用手册</a> </li>
				<li> <a href="#"> 2.Android疯狂讲义 </a> </li>
				<li> <a href="#"> 3.Office2010完全讲解 </a> </li>
			</ul>
		</figure>
	</article>
</section>


<footer>
	<p> Gizp: <?php echo $this->callVariableModifier('to_string', array (
  0 => $_ENV['bbs']['output_buff']['gizp_state'],
)); ?> Total Used time: <?php $this->callNormalFunction('total_used_time', array (
)); ?> (ms) View parse used time : <?php $this->callNormalFunction('view_used_time', array (
)); ?> (ms) </p>
	<p> Date: <?php echo $this->callVariableModifier('date_format', array (
  0 => $this->now,
)); ?> Copyright: <?php echo $_ENV['bbs']['copyright']; ?> </p>
</footer>
</body>
</html>

