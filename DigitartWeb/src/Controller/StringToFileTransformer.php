<?php
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;

class StringToFileTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        // Transform the file to a string for the form view
        if ($value instanceof File) {
            return $value->getPathname();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        // Transform the string back to a file
        if (is_string($value)) {
            return new File($value);
        }

        return $value;
    }
}
