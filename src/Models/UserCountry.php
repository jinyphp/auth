<?php

namespace Jiny\Auth\Models;

/**
 * Backward compatibility alias for UserCountry
 * @deprecated Use Jiny\Locale\Models\Country instead
 */
class_alias(\Jiny\Locale\Models\Country::class, UserCountry::class);