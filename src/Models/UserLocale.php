<?php

namespace Jiny\Auth\Models;

/**
 * Backward compatibility alias for UserLocale
 * @deprecated Use Jiny\Locale\Models\Locale instead
 */
class_alias(\Jiny\Locale\Models\Locale::class, UserLocale::class);