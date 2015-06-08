{include file="header.tpl" title="音乐播放器"}

<table id="music_list"></table>
<audio src="" id="player" controls autoplay> 浏览器不支持此元素！ </audio>
<input type="button" value="下一页" id="next" />
<input type="button" value="上一页" id="prev" />

<script>
	musicList	= {$music_list};
	perPageNum	= 20;
	doc			= document;
	table		= $("#music_list");
	nCell		= null;
	mCell		= null;
	tbRow		= null;
	state		= new Array();
	player		= $("#player");
	start		= 0;
	page		= 1;
	maxPage		= Math.ceil(musicList.length / perPageNum);
</script>

{no_compile}
<script>
try{
	$("#prev").onclick = function() {
		page--;
		if (page < 1) {
			page = 1;
			return;
		}
		showList();
	};

	$("#next").onclick = function() {
		page++;
		if (page > maxPage) {
			page = maxPage;
			return;
		}
		showList();
	};

	$("body").onload = function() {
		showList();
		player.src = '/t.php?m='+encodeURI(musicList[0]);
	};

	function showList() {
		start = perPageNum*(page-1);
		finish= start + perPageNum > musicList.length ? musicList.length: start + perPageNum;

		while (table.rows.length > 0) {
			table.deleteRow(0);
		}

		for (i = start; i < finish; i++) {
			tbRow	= doc.createElement('tr');
			nCell	= doc.createElement('tb');
			mCell	= doc.createElement('tb');

			mCell.innerHTML = '<a href="#" onclick="playMusic(this)">'+musicList[i]+'</a>';
			nCell.innerHTML = i;

			tbRow.appendChild(nCell);
			tbRow.appendChild(mCell);
			table.appendChild(tbRow);
		}
	}

	function playMusic(a) {
		name = a.innerHTML;
		player.play();
		player.src = '/t.php?m='+encodeURI(name);
	}
}catch(e) {
	alert(e);
}
</script>
{/no_compile}






{include file="footer.tpl"}