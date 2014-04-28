(function(){

  var users     = {}, 
    usrSearch = $('form#user-search'), 
    usrInput  = usrSearch.find('input[type="text"]');
    
  var usrlist      = $('#userlist'),
    userSearchForm = $('#userSearchForm'),
    pagination     = usrlist.find('.pagination'),
    sizesl         = usrlist.find('select[name="size"]'),
    page           = usrlist.data('page'), 
    size           = usrlist.data('size'),
    reset          = usrlist.find('#resetuserlist'),
    searchString   = userSearchForm.find('input[name="search"]').val();

  usrlist.each(function(){
    
    pagination.on('click', 'a', function(){
      page = $(this).data('page');
      usrlist.trigger('gridupdate');
      return false;
    });
    
    sizesl.on('change', function(){
      page = 1;
      size = $(this).val();
      usrlist.trigger('gridupdate');
      return false;
    }).val(size);
    
    reset.on('click', function(){
      size = '';
      page = 1;
      usrlist.trigger('gridupdate');
      return false;
    });

    usrlist.on('gridupdate', function(){
      window.location.href = '/admin/?size='+encodeURIComponent(size)+'&page='+encodeURIComponent(page)+'&search='+encodeURIComponent(searchString);
    });

    userSearchForm.on('submit', function(){
      size = '';
      page = 1;
      searchString = $(this).find('input[name="search"]').val();
      usrlist.trigger('gridupdate');
      return false;
    });
    
  });
  
})();