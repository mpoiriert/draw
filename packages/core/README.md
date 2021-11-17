# Core

This library is for the core of draw. 

The intend is to provide required reusable code that are needed by more than component/bundle.

## Ignore Annotations

Some component/bundle use annotations of other libraries that are **not** in the required section of composer.

If those annotations are parse by doctrine annotation reader it will throw a error if they are not ignored.

Instead of ignoring them yourself those yourself the core component will automatically detect draw namespaced annotations 
that are not present (via a configuration) and register them as ignore if doctrine annotation reader is present.

If you want to use it in your project check [ignore_annotations.php](ignore_annotations.php) for a example.