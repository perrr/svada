google.charts.load('current', {'packages':['line']}); // Update packages if new chart types are added

google.charts.setOnLoadCallback(loadStats);

function drawUserActivity(activity) {
  var data = new google.visualization.DataTable();
  data.addColumn('string', language['month']);
  for (var user in activity) {
    if (user == 'Total')
      user = language['total'];
    data.addColumn('number', user);
  }

  var rows = new Array();
  var i = 0;
  for (month in activity['Total']) {
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
      title: language['activityOverTime'],
      subtitle: language['inMsgPerMonth']
    },
    width: 700,
    height: 400
  };

  var chart = new google.charts.Line(document.getElementById('user_activity_graph'));
  chart.draw(data, options);
}

function drawDailyActivity(activity) {
  var data = new google.visualization.DataTable();
  data.addColumn('string', language['time']);
  data.addColumn('number', language['messages']);

  var rows = new Array();
  var i = 0;
  for (time in activity) {
    rows.push([]);
    rows[i].push(time);
    rows[i].push(activity[time]);
    i++;
  }

  data.addRows(rows);

  var options = {
    chart: {
      title: language['activityThroughDay'],
      subtitle: language['inMsgPerMin']
    },
    width: 700,
    height: 400
  };

  var chart = new google.charts.Line(document.getElementById('daily_activity_graph'));
  chart.draw(data, options);
}

function loadStats() {
  $.ajax({url: "statsGenerator.php", dataType: "json", success: function(result){
    drawUserActivity(result.userActivity);
    drawDailyActivity(result.dailyActivity);
  }});
}
