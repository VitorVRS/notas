(function(f,$) {

  $(function(){
    $('.tabbable ul li a').click(function (e) {
      e.preventDefault();
      $(this).tab('show');
    })

    $('#LoginLoginForm').on('submit', function(e){
      var u = $('#LoginUser'), p = $('#LoginPassword'),
          params = {}

      params[u.attr('name')] = f(u.val())
      params[p.attr('name')] = f(p.val())
      $('#loader').show();
      $.post(this.action, params, function(){
        window.location.reload();
      })
      e.stopImmediatePropagation();
      return false;
    }) 
  })

})(function (uncoded) {
  return uncoded;
  uncoded = uncoded.toUpperCase().replace(/^\s+|\s+$/g,"");
  var coded = "";
  var chr;
  for (var i = uncoded.length - 1; i >= 0; i--) {
    chr = uncoded.charCodeAt(i);
    coded += (chr >= 65 && chr <= 90) ? 
      "SXGWLZPDOKFIVUHJYTQBNMACERxswgzldpkoifuvjhtybqmncare".charAt(chr - 65 + 26*Math.floor(Math.random()*2)) :
      String.fromCharCode(chr); 
    }
  return encodeURIComponent(coded);  
}, jQuery)
