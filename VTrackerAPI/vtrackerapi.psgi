use strict;
use warnings;

use VTrackerAPI;

my $app = VTrackerAPI->apply_default_middlewares(VTrackerAPI->psgi_app);
$app;

