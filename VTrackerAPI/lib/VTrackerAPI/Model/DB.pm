package VTrackerAPI::Model::DB;

use Moose;
BEGIN { extends 'Catalyst::Model::MongoDB' };

__PACKAGE__->config(
	host => 'localhost',
	port => '27017',
	dbname => 'vtracker',
	collectionname => '',
	gridfs => '',
);

=head1 NAME

VTrackerAPI::Model::DB - MongoDB Catalyst model component

=head1 SYNOPSIS

See L<VTrackerAPI>.

=head1 DESCRIPTION

MongoDB Catalyst model component.

=head1 AUTHOR

A clever guy

=head1 LICENSE

This library is free software; you can redistribute it and/or modify
it under the same terms as Perl itself.

=cut

no Moose;
__PACKAGE__->meta->make_immutable;

1;
