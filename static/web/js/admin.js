(function(){

  var GraphUtil = {};

  GraphUtil.prepareGraphData = function prepareGraphData ( data, property, timeRange, timeUnit ) {
    var graphData = {};
    switch (timeUnit.toUpperCase()) {
      case 'DAYS':
        graphData = GraphUtil.fillGraphDates(data, property, timeRange, timeUnit, 'YYYY-MM-DD', 'MM/D', '');
        break;
      case 'MONTHS':
        graphData = GraphUtil.fillGraphDates(data, property, timeRange, timeUnit, 'YYYY-MM', 'YY/MM', '-01');
        break;
      case 'YEARS':
        graphData = GraphUtil.fillGraphDates(data, property, timeRange, timeUnit, 'YYYY', 'YYYY', '-01-01');
        break;
    }
    return graphData;
  };

  GraphUtil.fillGraphDates = function fillGraphDates ( data, property, timeRange, timeUnit, format1, format2, addon ) {
    var dataSet = [],
        dataLabels = [],
        dates = [],
        a = moment({hour: 1}).subtract(timeRange, timeUnit),
        b = moment({hour: 1});
    for (var m = a; m.isBefore(b) || m.isSame(b); m.add(1, timeUnit)) {
      dates.push(m.format(format1)+addon);
      dataLabels.push(m.format(format2));
      dataSet.push(0);
    }
    for(var i=0; i<data.length; ++i){
      var x = dates.indexOf(data[i].date);
      if(x != -1)
        dataSet[x] = data[i][property];
    }
    return {
      labels: dataLabels,
      data: dataSet
    };
  };

  GraphUtil.formatCurrency = function formatCurrency(label) {
    return  '$' + label.toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  };

  window.GraphUtil = GraphUtil;

})();

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
      window.location.href = '/admin/users/?size='+encodeURIComponent(size)+'&page='+encodeURIComponent(page)+'&search='+encodeURIComponent(searchString);
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