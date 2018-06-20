function hithere(){
    alert('hi there');
}

function addCss(){
    var cssId = 'inourplace_1_0_0_css';
    
    //if (!document.getElementById(cssId)) 
    {
        var head = document.getElementsByTagName('head')[0];
        var link=document.createElement('link');
        link.id=cssId;
        link.rel='stylesheet';
        link.type='text/css';
        link.href='media/com_isearch/css/inourplace.1.0.0.css';
        link.media='all';
        head.appendChild(link);
    }
}


function getHeightKeeperHeight() {
    var bar = document.getElementById('nav_height_keeper');

    return bar.offsetHeight+'px';
    
}

//https://stackoverflow.com/questions/1216114/how-can-i-make-a-div-stick-to-the-top-of-the-screen-once-its-been-scrolled-to#2153775
var startProductBarPos=-1;
    window.onscroll=function(){
    var bar = document.getElementById('nav_height_keeper');
    if ( startProductBarPos < 0 ) {
        startProductBarPos=findPosY(bar);
    }
    
    if( pageYOffset > startProductBarPos ) {
        bar.style.position='fixed';
        bar.style.top=0;
        bar.style.paddingLeft='95px';
        document.getElementById("nav_height_keeper_inline").style.height = bar.offsetHeight+'px';
        
        var vid_vis=document.getElementById("nav_height_keeper_body");
        var vid_vis_style = window.getComputedStyle(vid_vis);
        if ( vid_vis_style.getPropertyValue('display') != 'none' ) {
            document.getElementById("iop_logo_icon").style.position = 'fixed';
            document.getElementById("iop_logo_icon").style.top = '10px';
            document.getElementById("iop_logo_icon").style.left = '45px';
        }
        
    } else {
        bar.style.position='relative';
        bar.style.paddingLeft='0px';
        document.getElementById("iop_logo_icon").style = 'default';
        document.getElementById("iop_logo_icon").style.position = 'relative';
        document.getElementById("iop_logo_icon").style.top = '0';
        document.getElementById("iop_logo_icon").style.left = '0';
        
    }
};

function findPosY(obj) {
  var curtop = 0;
  if (typeof (obj.offsetParent) != 'undefined' && obj.offsetParent) {
    while (obj.offsetParent) {
      curtop += obj.offsetTop;
      obj = obj.offsetParent;
    }
    curtop += obj.offsetTop;
  }
  else if (obj.y) {
    curtop += obj.y;
  }

  return curtop;
}


function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}

