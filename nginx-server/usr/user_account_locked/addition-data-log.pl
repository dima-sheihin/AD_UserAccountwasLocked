#!/usr/bin/perl -w

use strict;
use warnings;
use Data::Dumper;
use utf8;
use POSIX;
use JSON;

binmode(STDOUT,':utf8');

# $ARGV[0] = 'addition-data-log';

if ( ! defined $ARGV[0] ) {
  print "No arg, exit\n";
  exit;
  }

my $log_patch = "/usr/user_account_locked/log/";


if ( $ARGV[0] eq 'addition-data-log' ) {
  #my $new_account_locked_file = '/usr/user_account_locked/test_account_locked_1.txt';
  my $new_account_locked_file = '/tmp/addition-data-log/account_locked.txt';
  if ( ! -f $new_account_locked_file ) {
    print "filename-json-new-error-does-not-exist<p>";
    exit;
    }
  my $new_account_locked_text = undef;
  if ( open ( FILE , '<' , $new_account_locked_file ) ) {
    my $pos=0;
    while ( my $row = <FILE> ) {
      $new_account_locked_text .= $row;
      }
    close FILE;
    }
  my %new_account_locked_hash;
  if ( defined $new_account_locked_text and length($new_account_locked_text) > 5 ) {
    eval { %new_account_locked_hash = %{decode_json($new_account_locked_text)}; }
    }

  if ( ! keys %new_account_locked_hash ) {
    print "filename-json-error-not-hash<p>";
    exit;
    }
  my $date = POSIX::strftime('%Y_%m_%d',localtime); # format 2021-11-23
  $log_patch .= "account_locked_$date.log";

  if ( open FILE, ">> $log_patch" ) {
    binmode( FILE,":utf8");
    foreach my $str ( keys %new_account_locked_hash ) {
      my $json = to_json( \%{$new_account_locked_hash{$str}} , { 'pretty' => 1, 'utf8' => 1 } );
      $json .=",";
      print FILE $json;
      }
    close FILE;
    }
  unlink ( $new_account_locked_file );
  print "filename-log-complete<p>";
  }
