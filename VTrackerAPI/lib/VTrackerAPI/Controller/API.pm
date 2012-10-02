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

sub new_key :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') {}

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

sub get_categories :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') { }

sub get_categories_GET {
	my ( $self, $c ) = @_;
	my $cursor = $c->fetchDocuments( 'categories', {} );
	my $categories = { categories => [] };
	my @results = ();
	my $params = $c->req->query_params;
	
	# defaults
	$params->{'type'} = 'popular' unless defined( $params->{'type'} );
	$params->{'sort'} = 'popular' unless defined( $params->{'sort'} );
	$params->{'ids'} = [] unless defined( $params->{'ids'} );
	$params->{'count'} = 10 unless defined( $params->{'count'} );
	
	# sort the resultset
	if( $params->{'sort'} eq 'alpha' ){
		$cursor->sort( { name => 1 } );
	}
	elsif( $params->{'sort'} eq 'popular' ){
		$cursor->sort( { likes => 1 } );
	}
	
	$cursor->limit( $params->{'count'} );
	
	# narrow the resultset	
	if( $params->{'type'} eq 'all' ){
		@results = $cursor->all;
	}
	elsif( $params->{'type'} eq 'custom' ){
		@results = $cursor->all;
	}
	elsif( $params->{'type'} eq 'popular' ){
		@results = $cursor->all;
	}
	else {
		return $self->status_not_found( $c, message => "Invalid type parameter" );
	}
	
	foreach my $result ( @results ){ $result->{'_id'} = "$result->{'_id'}" };
	$categories->{'categories'} = \@results; 
	return $self->status_ok( $c, entity => $categories );
}

=head1 AUTHOR

A clever guy

=head1 LICENSE

This library is free software. You can redistribute it and/or modify
it under the same terms as Perl itself.

=cut

__PACKAGE__->meta->make_immutable;

__PACKAGE__->config(
	'default'		=> 'application/json',
	'map'		=> {
		'application/json'	=> 'JSON',
	},
);

1;
