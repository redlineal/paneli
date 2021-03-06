<?php

namespace Amghost\Http\Requests\Api\Application\Locations;

use Amghost\Models\Location;

class GetLocationRequest extends GetLocationsRequest
{
    /**
     * Determine if the requested location exists on the Panel.
     *
     * @return bool
     */
    public function resourceExists(): bool
    {
        $location = $this->route()->parameter('location');

        return $location instanceof Location && $location->exists;
    }
}
