#!/usr/bin/perl
use strict;
use warnings;
use MongoDB;


my $ip = '127.0.0.1';
my $api_key = '00000000000000000000000000000000';
my $count = shift;
my $mongo = MongoDB::Connection->new(); # defaults to localhost with no auth
my $database = $mongo->vtracker;
my $reports = $database->reports;


