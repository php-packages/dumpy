# Dumpy

## Data type representation

### Inherited behavior

- integers
- floats (doubles)
- booleans
- null values

### Additions

- strings:
    - ignore *newline characters*
    - cut off the string if it is *too* long
- arrays:
    - use square brackets
    - proper indenting (handle nested arrays properly)
    - hide *some* elements if there are too many
- objects:
    - show the object's fully qualified class name and its hash
    - show its parent classes (abstract and non-abstract) and interfaces
    - show its properties and their respective values
    - show its methods (name, visibility, parameters)

I've been thinking about utilising docblock comments.
