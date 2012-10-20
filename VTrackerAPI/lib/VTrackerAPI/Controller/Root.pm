package VTrackerAPI::Controller::Root;
use Moose;
use namespace::autoclean;
use MongoDB;

BEGIN { extends 'Catalyst::Controller' }

#
# Sets the actions in this controller to be registered with no prefix
# so they function identically to actions created in MyApp.pm
#
__PACKAGE__->config(namespace => '');

=head1 NAME

VTrackerAPI::Controller::Root - Root Controller for VTrackerAPI

=head1 DESCRIPTION

[enter your description here]

=head1 METHODS

=head2 index

The root page (/)

=cut

sub index :Path :Args(0) {
    my ( $self, $c ) = @_;

    # Hello World
    $c->response->body( $c->welcome_message );
}

=head2 default

Standard 404 error page

=cut

sub default :Path {
    my ( $self, $c ) = @_;
    $c->response->body( 'Page not found' );
    $c->response->status(404);
}

=head2 end

Attempt to render a view, if needed.

=cut

sub end : ActionClass('RenderView') {}

sub auto :Local {
	my ( $self, $c ) = @_;
	
	# this forces mongo to store numbers as numbers instead of strings
	$MongoDB::BSON::looks_like_number = 1;
}

sub createDocument :Private {
	my ( $c, $collection, $doc ) = @_;
	
	return $c->model( 'DB' )->collection( $collection )->insert( $doc );
}

sub deleteDocument :Private {
	my ( $c, $collection, $doc_id ) = @_;

	return $c->model( 'DB' )->collection( $collection )->remove( { '_id' => MongoDB::OID->new( 'value' => $doc_id ) } );
}

sub updateDocument :Private {
	my ( $c, $collection, $doc_id, $doc ) = @_;
	
	return $c->model( 'DB' )->collection( $collection )->update( { '_id' => MongoDB::OID->new( 'value' => $doc_id ) }, $doc );
}

sub fetchDocuments : Private {
	my ( $c, $collection, $params, $sort_params, $limit, $skip ) = @_;
	
	# only OIDify this if it's an OID!
	if( defined( $params->{'_id'} ) and ( $params->{'_id'} =~ m/^[0-9a-f]{24}$/ ) ){
		$params->{'_id'} = MongoDB::OID->new( 'value' => $params->{'_id'} );
	}
	
	my $docs = $c->model( 'DB' )->collection( $collection )->find( $params );
	
	if( $sort_params ){ $docs = $docs->sort( $sort_params ) }
	if( $skip ){ $docs = $docs->skip( $skip ) }
	if( $limit ){ $docs = $docs->limit( $limit ) }
	
	return( $docs );
}

=head1 AUTHOR

Catalyst developer

=head1 LICENSE

This library is free software. You can redistribute it and/or modify
it under the same terms as Perl itself.

=cut

__PACKAGE__->meta->make_immutable;

1;
