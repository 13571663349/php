{include file="header.tpl" title="首页"}

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

{include file="footer.tpl"}