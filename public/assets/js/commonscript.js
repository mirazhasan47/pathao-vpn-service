function showMessage(type, message) {
	new Noty({
		type: type,
		layout: 'bottomRight',
		text: message,
		progressBar: true,
		timeout: 2500,
		animation: {
			open: 'animated bounceInRight', 
			close: 'animated bounceOutRight' 
		}}).show()
}