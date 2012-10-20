package VTrackerAPI::Controller::API;
use Moose;
use namespace::autoclean;
use base 'Catalyst::Controller::REST';

BEGIN { extends 'Catalyst::Controller::REST'; }

=head1 NAME

VTrackerAPI::Controller::API - Catalyst Controller

=head1 DESCRIPTION

Catalyst Controller.

=head1 METHODS

=cut


=head2 index

=cut


sub index :Path :Args(0) {
    my ( $self, $c ) = @_;

    $c->response->body('Matched VTrackerAPI::Controller::API in API.');
}

sub new_key :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') {
	my ( $self, $c ) = @_;
	
#	$c->forward( 'View::JSON' );
	
}

sub new_key_POST {
	my ( $self, $c ) = @_;
	my $data = $c->req->data;
		
	$data->{'name'} = 'John Doe' unless $data->{'name'};
	$data->{'email'} = '' unless $data->{'email'};
	
	my $id = $c->createDocument(
		'keys',
		{
			name		=> $data->{'name'},
			email		=> $data->{'email'},
			enabled		=> 1,
			created		=> time,
			ip_address	=> '0.0.0.0',
			reports		=> [],
		}
	);
	
	return $self->status_no_content( $c, message => 'ERROR' ) if not defined( $id );
	return $self->status_created( $c, location => $c->req->uri->as_string, entity => { key => "$id" } );
};
	
	

=head1 AUTHOR

A clever guy

=head1 LICENSE

This library is free software. You can redistribute it and/or modify
it under the same terms as Perl itself.

=cut

__PACKAGE__->meta->make_immutable;

1;
