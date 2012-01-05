


var open_id;

function changeTab(id){
	var a=document.getElementById(id);

	a.style.display='block';
	if(open_id)	
		open_id.style.display='none';
	open_id=a;

	
}
