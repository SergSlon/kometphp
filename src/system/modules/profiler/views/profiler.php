<?php
$ev_listened = array();
$ev_notlistened = array();
foreach ($data["events"] as $i => $ev) {
    if ($ev["listened"] > 0)
        $ev_listened[] = $ev;
    else
        $ev_notlistened[] = $ev["event"];
}
?>
<div class="profiler-bar navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
      <div class="pull-left">
          <a class="brand" href="#">KometPHP Profiler</a>
          <ul class="nav">
              <li><a title="Request info" href="#profiler-request" data-toggle="tab"> Request: <span class="badge badge-info">/<?php echo \Komet\Format::truncate($data["info"]["called_uri"], 20, "...") ?></span></a></li>
              <li><a href="#profiler-logs" data-toggle="tab">Logs <span class="badge"><?php echo count($data["logs"]) ?></span> </a></li>
              <li><a title="Triggered events" href="#profiler-events" data-toggle="tab">Events <span class="badge"><?php echo count($ev_listened) ?></span> </a></li>
              
              <li><a title="Load time in seconds" href="#"><i class="icon-time"></i> <?php echo $data["info"]["elapsed_time"] ?> s</a></li>
              <li><a title="Used memory" href="#"><i class="icon-save"></i> <?php echo $data["info"]["used_memory"]; ?> MB</a></li>
              <li><a title="Environment name" href="#"> ENV=<?php echo $data["info"]["env"]->name ?></a></li>
              <li><a title="KometPHP version" href="#"> v<?php echo \Komet\VERSION ?></a></li>
          </ul>
      </div>
      <ul class="nav pull-right">
          <li><a href="#" data-event="profiler.resize"><i class="icon-resize-full"></i></a></li>
      </ul>
  </div>
</div>

<div class="profiler-container container">
    <div class="tab-content">
        <div id="profiler-request" class="tab-pane">
            <h1>Request Info</h1>
            <table class="table">
                <tr><th>Called URI</th> <td><span class="badge badge-info">/<?php echo $data["info"]["called_uri"]; ?></span></td></tr>
                <tr><th>Segmented params</th> <td><?php echo $data["info"]["segmented_params"]; ?></td></tr>
                <tr><th>Called Action</th> <td><?php echo $data["info"]["called_action"]; ?></td></tr>
                <tr><th>Module</th> <td><?php echo $data["info"]["controller_module"]; ?></td></tr>
                <tr><th>Loaded Modules</th> <td><?php echo $data["info"]["loaded_modules"]; ?></td></tr>
                <tr><th>GET params</th> <td><pre><?php print_r($data["info"]["get_vars"]); ?></pre></td></tr>
                <tr><th>POST params</th> <td><pre><?php print_r($data["info"]["post_vars"]); ?></pre></td></tr>
                <tr><th>COOKIES</th> <td><pre><?php print_r($data["info"]["cookie_vars"]); ?></pre></td></tr>
            </table>
        </div>
        <div id="profiler-logs" class="tab-pane">
            <h1>Logs</h1>
            <!--<pre><?php //print_r($data); ?></pre>-->
            <ul>
                <?php
                foreach ($data["logs"] as $i => $log):
                    if ($log["channel"]) {
                        $channel = '<strong>' . $log["channel"] . '</strong>: ';
                    }else
                        $channel = '';

                    $lvname = \Komet\Logger\Logger::getLevelName($log["level"]);
                    switch ($lvname) {
                        case "debug":
                        case "undefined": {
                                $alert_type = "debug";
                                $alert_icon = '<i class="icon-chevron-right"></i> ';
                            } break;
                        case "info": {
                                $alert_type = "info";
                                $alert_icon = '<i class="icon-info-sign"></i> ';
                            } break;
                        case "warning": {
                                $alert_type = "warning";
                                $alert_icon = '<i class="icon-exclamation-sign"></i> ';
                            } break;
                        case "error": {
                                $alert_type = "error";
                                $alert_icon = '<i class="icon-remove-sign"></i> ';
                            } break;
                        case "critical": {
                                $alert_type = "critical";
                                $alert_icon = '<i class="icon-remove-sign"></i> ';
                            } break;
                        default: $alert_type = $lvname;
                            break;
                    }
                    
                    $hasData = isset($log["data"]) and !empty($log["data"]);
                    ?>
                <li>
                    <div class="alert alert-<?php echo $alert_type ?>">
                        <div class="row-fluid">
                            <?php if(isset($log["data"]) and !empty($log["data"])): ?>
                            <div class="span12">
                                <?php echo $alert_icon.$channel.$log["message"] ?>
                            </div>
                            <div class="span12">
                                <pre>data:<?php print_r($log["data"]);  ?></pre>
                            </div>
                            <?php else: ?>
                            <div class="span12">
                                <?php echo $alert_icon.$channel.$log["message"] ?>
                            </div>
                            <?php endif;?>
                        </div>
                    </div>
                </li>
            <?php
            endforeach; ?>
            </ul>
        </div>

        <div id="profiler-events" class="tab-pane">
            <h1>Events</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Triggered event</th>
                        <th>Attached listeners #</th>
                        <th>Elapsed time</th>
                        <th>Used memory</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach($ev_listened as $i => $ev):
                        $mem = $ev["end_mem"] - $ev["start_mem"];
                        if ($mem < 0)
                            $mem = 0;

                        $mem = round(($mem / 1024) / 1024, 4);
                    ?>
                <tr>
                    <td><span class="badge"><?php echo $ev["event"] ?></span></td>
                    <td><?php echo $ev["listened"] ?></td>
                    <td><?php echo \Komet\Date::elapsedTime($ev["start_time"], $ev["end_time"], 4, false) ?> s</td>
                    <td><?php echo $mem ?> MB</td>
                </tr>
                <?php
                endforeach; ?>
                    <tr><td colspan="4">&nbsp;</td></tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Not listened events:</th>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-size:85%"><?php echo implode(', ', $ev_notlistened); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    .alert pre{
        background:#fff;
        border:1px solid #777;
        color:#444;
        margin:20px 20px 20px 0;
        font-size:11px;
    }
</style>

<!--

To-Do:

- Count errors and warnings
- show data, caller and microtime for logs

-->