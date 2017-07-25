StatsD plugin for Known
=======================

StatsD is a Node.JS stats server created by the people at etsy to provide a simple way of logging useful statistics from software.

These statistics are an invaluable way of monitoring the performance of your application, monitoring the performance of software
changes and diagnosing faults.

This is an Known plugin for interfacing your application to a StatsD server.

What this plugin does
---------------------
This plugin gives you an overview of what is happening in your Known install by logging important system level things - events, errors, exceptions etc.

This lets you get a very clear idea of how your Known network is performing, and quickly see the effect that changes have on your users.

Installation
------------
 * Install Node.JS, either from git-hub (https://github.com/joyent/node) or the package manager for your OS
 * Install StatsD, available from https://github.com/etsy/statsd
 * Not required, but highly recommended, install a Graphite server for graph visualisation (http://graphite.wikidot.com/start)
 * Place this plugin in IdnoPlugins/StatsD
 * Add the following to your ```config.ini```

```
statistics_collector = IdnoPlugins\StatsD\StatsDStatisticsCollector;
statsd_enabled = true;
```

Optionally you can specify the following configuration options:

```
statsd_host = localhost
statsd_port = 8125
statsd_bucket = some_name
statsd_samplerate = 1 
```

```statsd_samplerate``` being the default sample rate, on busy systems you might want to set this to something like 0.1, to save every one in ten actions

If all the backend and infrastructure stuff has been set up correctly you should now be recording numerous performance statistics about your Known installation.

See
---
 * Author: Marcus Povey <http://www.marcus-povey.co.uk>
 * Node.JS <https://github.com/joyent/node>
 * StatsD <https://github.com/etsy/statsd>
 * Graphite Graphing Server <http://graphite.wikidot.com/start>
