window.showMedia = function() {
  let mediaWindow = null;

  return function() {
    const width = Math.min(window.innerWidth-80, 1000);
    const height = Math.min(window.innerHeight-80, 720);
    const left = window.screenX + (window.outerWidth - width)/2;
    const top = window.screenY + (window.outerHeight - window.innerHeight) + (window.innerHeight - height)/2;

    if (!mediaWindow || mediaWindow.closed()) {
      mediaWindow = window.open(
        '/admin/medias/select',
        'chooseMedia',
        `resizable,scrollbars,status,top=${top},left=${left},width=${width},height=${height}`
      );
    } else {
      mediaWindow.focus()
    }
  }
}();

function recieveMediaUrl(url) {
  if (window.app && typeof window.app.recieveMediaUrl === 'function') {
    window.app.recieveMediaUrl(url)
  } else {
    alert(url)
  }
}
