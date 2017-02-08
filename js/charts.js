google.charts.load('current', {'packages':['line']}); // Update packages if new chart types are added



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
      //subtitle: language['inMsgPerMonth'],
	  chartArea: {  width: "50%", height: "70%" }
    },
    width: 800,
    height: 400
  };

  var chart = new google.charts.Line(document.getElementById('user_activity_graph'));
  chart.draw(data, google.charts.Line.convertOptions(options));
}

function drawDailyActivity(activity) {
  var data = new google.visualization.DataTable();
  data.addColumn('string', language['time']);
  for (var user in activity) {
    if (user == 'Total')
      user = language['total'];
    data.addColumn('number', user);
  }

  var rows = new Array();
  var i = 0;
  for (time in activity['Total']) {
    rows.push([]);
    rows[i].push(time);
    for (user in activity) {
      rows[i].push(activity[user][rows[i][0]]);
    }
    i++;
  }

  data.addRows(rows);

  var options = {
    chart: {
      title: language['activityThroughDay'],
      //subtitle: language['inMsgPerMin'],
	  chartArea: {  width: "50%", height: "70%" }
    },
    width: 800,
    height: 400
  };

  var chart = new google.charts.Line(document.getElementById('daily_activity_graph'));
  chart.draw(data, google.charts.Line.convertOptions(options));
  chart.update
}

function loadStats() {
  $.ajax({url: "statsGenerator.php", dataType: "json", success: function(result){
    drawUserActivity(result.userActivity);
    drawDailyActivity(result.dailyActivity);
  }});
}
