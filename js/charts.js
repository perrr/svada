google.charts.load('current', {'packages':['line']}); // Update packages if new chart types are added

google.charts.setOnLoadCallback(loadStats);

function drawActivity(activity) {
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Month');
  for (var user in activity) {
    var username = user;
    data.addColumn('number', user);
  }

  var rows = new Array();
  var i = 0;
  for (month in activity[username]) {
    rows.push([]);
    rows[i].push(month);
    for (user in activity) {
      rows[i].push(activity[user][rows[i][0]]);
    }
    i++;
  }

  data.addRows(rows);

  var options = {
    chart: {
      title: 'Activity per User over Time',
      subtitle: 'in posts per month'
    },
    width: 700,
    height: 400
  };

  var chart = new google.charts.Line(document.getElementById('activity_graph'));
  chart.draw(data, options);
}

function loadStats() {
  $.ajax({url: "statsGenerator.php", dataType: "json", success: function(result){
    drawActivity(result.activity);
  }});
}
