<?php declare(strict_types=1);

namespace PhpSlides\Core\Traits\Resources;

/**
 * Resources Trait
 *
 * This trait aggregates different resource-related functionalities, including
 * routing and API resources. It uses other traits like `RouteResources` and
 * `ApiResources` to provide a set of methods and properties that can be reused
 * across multiple classes.
 *
 * It is useful for managing and handling routes and API resources in one place,
 * keeping your code organized and ensuring that resource management is reusable.
 */
trait Resources
{
	/**
	 * Incorporates route-related functionalities from the RouteResources trait.
	 */
	use RouteResources;

	/**
	 * Incorporates API-related functionalities from the ApiResources trait.
	 */
	use ApiResources;

}
