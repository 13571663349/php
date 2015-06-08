{include file="header.tpl" title="{:$err_msg}"}

<div style="background-color:#000; color:#fff; font-size:24; font-weight:bold;">
	错误详情：{$err_msg}
</div>
<p style="margin: 0; border: #C60 dashed 2px">
	在文件"{$err_file}"{$err_line}行，错误代号:{$err_code}
<pre>
	回溯：{$err_trace}
</pre>
</p>

{include file="footer.tpl"}