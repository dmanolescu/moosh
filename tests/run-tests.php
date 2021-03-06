<?php
/**
 * moosh - Moodle Shell
 *
 * @copyright  2012 onwards Tomasz Muras
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once '../includes/functions.php'; // everything is hardcoded for now

function get_commands_list($moodle_ver) {
    run_external_command("cp config$moodle_ver.sh config.sh", "Couldn't copy tests config file");
    $file = file_get_contents("config.sh");

    preg_match("/(?<=MOODLEDIR=)(.*)/", $file, $moodledir);
    exec("cd $moodledir[0] && moosh", $output);

    $commands_list = array();

    foreach ($output as $line) {
        if(preg_match("/(?<=\t)(.*)/", $line, $result)) {
            $commands_list[] = $result[0];
        }
    }
    return $commands_list;
}

function run_tests(array $commands) {

	$results = array();
    foreach ($commands as $command) {

        if ($command == null) { // skip empty lines
            continue;
        }

        //check if test exists for command
        $testfile = $command .'.sh';

        if(!file_exists($testfile)) {
            echo "No test for ". $command. "\n";
            $results[$command] = "unknown";
            continue;
        }

        $output = NULL;
        echo "Executing '$testfile' in ". getcwd() ."\n";
        exec("./$testfile", $output, $ret);
        echo "Return: $ret\n";

        if($ret == 127) {
            die("File not found? That should not happen.\n");
        }

        if ($ret == 0) {
            $results[$command] = "pass";
        } else {
            $results[$command] = "fail";
            var_dump($output);
            echo "\n";
            die();
        }
    }
    return $results;
};

$table = '---
title: CI
layout: default
---

CI
========';
$out = '<div class="table-responsive">
	<table class="table table-striped table-bordered table-hover">
	<thead>
	  <tr>
		<th></th>
		<th>Moodle 2.6</th>
		<th>Moodle 2.5</th>
		<th>Moodle 2.3</th>
		<th>Moodle 2.2</th>
		<th>Moodle 2.1</th>
		<th>Moodle 1.9</th>
	  </tr>
	</thead>
	<tbody>';

$all_commands = get_commands_list("26"); // this is ugly, disregard
$support_versions = array('19','21','22','23','24','25','26');

$results = array();
foreach($support_versions as $version) {
    $results[$version] = array();
    foreach($all_commands as $command) {
        $results[$version][$command] = 'not implemented';
    }
}

$moodle26 = run_tests(get_commands_list("26"));
foreach($moodle26 as $k=>$v) {
    $results['26'][$k] = $v;
}

$moodle25 = run_tests(get_commands_list("25"));
foreach($moodle25 as $k=>$v) {
    $results['25'][$k] = $v;
}


foreach ($all_commands as $command) {

	$out .= "<tr><td>$command</td>";

	foreach ($support_versions as $moodle) {
        //if($results[$moodle][])
        $out .=  '<td>' .$results[$moodle][$command] .'</td>';
            /*
		if (array_key_exists($command[0], $moodle)) {
			$result = $moodle[$command[0]];

			if ($result == "pass") {
				$table .= "<td><i class=\"fa fa-check\"></i></td>\n";
			} else {
				$table .= "<td><i class=\"fa fa-times\"></i></td>\n";
			}
		} else {
			$table .= "<td></td>";
		}
            */
	}
    $out .= '</tr>';
}

$out .= "	</tbody>
	</table>
	</div>";

file_put_contents("out.txt", $out);