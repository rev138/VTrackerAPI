package VTrackerAPI::Controller::API;
use Moose;
use namespace::autoclean;
use DateTime;
#use base 'Catalyst::Controller::REST';

BEGIN { extends 'Catalyst::Controller::REST'; }

__PACKAGE__->config(
	'default'	=> 'application/json',
	'map'	=> {
		'application/json'	=> 'JSON',
	},
);

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
			ip_address	=> $c->req->address,
			reports		=> [],
		}
	);
	
	return $self->status_no_content( $c, message => 'ERROR' ) if not defined( $id );
	return $self->status_created( $c, location => $c->req->uri->as_string, entity => { key => "$id" } );
};

sub get_categories :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') { }

sub get_categories_GET {
	my ( $self, $c ) = @_;
	my $categories = { categories => [] };
	my @results = ();
	my $params = $c->req->query_params;
	my %query = ();
	
	$query{'_id'} = $params->{'_id'} if defined( $params->{'_id'} );
		
	my $cursor = $c->fetchDocuments( 'categories', \%query );
	
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
	
	if( $params->{'species'} ){
		foreach my $result ( @results ){
			my @species = ();
			my $items = $result->{'species'};
						
			foreach my $item ( @$items ){
				my $doc = $c->fetchDocuments( 'species', { '_id' => $item } )->next;
				$species[@species] = $doc;
			}
		
			$result->{'species'} = \@species;
		}
	}
	
	# stringify the oid
	foreach my $result ( @results ){ $result->{'_id'} = "$result->{'_id'}" };
	
	$categories->{'categories'} = \@results; 
	return $self->status_ok( $c, entity => $categories );
}

sub submit_report :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') { }

sub submit_report_POST {
	my ( $self, $c ) = @_;
	my $data = $c->req->data;
	my $now = time;
	my $dt = DateTime->from_epoch( epoch => $now, time_zone => 'local' );
	
	$data->{'key'} = '' unless defined( $data->{'key'} );
	
	return $self->status_not_found( $c, message => 'Invalid API key' ) unless  validate_key( $c, $data->{'key'} );

	my $id = $c->createDocument(
		'reports',
		{
			api_key		=> $data->{'key'},
			ip_address 	=> $c->req->address,
			location 		=> {
				lat_long	=> [ $data->{'latitude'}, $data->{'longitude'} ],
				altitude	=> $data->{'altitude'},
			},
			attributes	=> $data->{'attributes'},
			'time'		=> {
				epoch		=> $now,
				string		=> $dt->day_abbr . ' ' . $dt->month_abbr . ' ' . $dt->day . ' ' . $dt->hour . ':' . $dt->minute . ':' . $dt->second . ' ' . $dt->time_zone_short_name . ' ' . $dt->year,
				year			=> $dt->year,
				month		=> $dt->month,
				day			=> $dt->day,
				hour		=> $dt->hour,
				minute		=> $dt->minute,
				second		=> $dt->second,
				timezone	=> $dt->time_zone_short_name,
			},
		}
	);


	return $self->status_no_content( $c, message => 'ERROR' ) if not defined( $id );
	return $self->status_created( $c, location => $c->req->uri, entity => { '_id' => "$id" } );
}


sub get_reports :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') { }

sub get_reports_GET {
	my ( $self, $c ) = @_;
	my $reports = { reports => [] };
	my @results = ();
	my $params = $c->req->query_params;
	my %query = ();
	
	$query{'_id'} = $params->{'_id'} if defined( $params->{'_id'} );
	$query{'api_key'} = $params->{'key'} if defined( $params->{'key'} );
	$query{'attributes.species'} = $params->{'species'} if defined( $params->{'species'} );
		
	my $cursor = $c->fetchDocuments( 'reports', \%query );
	my $count = $cursor->count;
	
	$params->{'limit'} = 1000 unless defined( $params->{'limit'} );
	$cursor->skip( int( $params->{'skip'} ) ) if defined( $params->{'skip'} );
	$cursor->limit( $params->{'limit'} );
	
	@results = $cursor->all;

	if( $params->{'expanded'} ){
		foreach my $result ( @results ){
			my $item = $result->{'attributes'}->{'species'};	
			my $doc = $c->fetchDocuments( 'species', { '_id' => $item } )->next;
			
			$result->{'attributes'}->{'species'} = $doc;
		}
	}
		
	# stringify the oid
	foreach my $result ( @results ){ $result->{'_id'} = "$result->{'_id'}" };
	
	$reports->{'reports'} = \@results;
	$reports->{'count'} = $count;
	 
	return $self->status_ok( $c, entity => $reports );
}

sub get_species :Local :Path( '/api' ) :Args( 0 ) :ActionClass('REST') { }

sub get_species_GET {
	my ( $self, $c ) = @_;
	my $species = { species => [] };
	my @results = ();
	my $params = $c->req->query_params;
	my %query = ();

	$query{'_id'} = $params->{'_id'} if defined( $params->{'_id'} );
		
	my $cursor = $c->fetchDocuments( 'species', \%query );
	my $count = $cursor->count;
	
	$params->{'limit'} = 100 unless defined( $params->{'limit'} );
	$cursor->skip( int( $params->{'skip'} ) ) if defined( $params->{'skip'} );
	$cursor->limit( $params->{'limit'} );
	
	@results = $cursor->all;
	
	# stringify the oid
	foreach my $result ( @results ){ $result->{'_id'} = "$result->{'_id'}" };
	
	$species->{'species'} = \@results;
	$species->{'count'} = $count;
	 
	return $self->status_ok( $c, entity => $species );
}
	
	
sub validate_key :Private {
	my ( $c, $key ) = @_;
	
	return $c->fetchDocuments( 'keys', { '_id' => $key,  enabled => 1 } )->count;
}

=head1 AUTHOR

A clever guy

=head1 LICENSE

This library is free software. You can redistribute it and/or modify
it under the same terms as Perl itself.

=cut

__PACKAGE__->meta->make_immutable;



1;
