#!/usr/bin/env python
import sys
from sys import argv

script, what_he_said, the_other_thing, somthing_else, uno_mass = argv

filename = "file.txt"

print "This is what you submitted: %s, %s and %s." % (what_he_said, the_other_thing, somthing_else)
sys.stdout.flush()
print "Opening the file..."
sys.stdout.flush()


target = open(filename, 'w')

print "Truncating the file.  Goodbye!"
target.truncate()

print "Now the program is writing %s to a file called %s." % (what_he_said, filename)

target.write(what_he_said)
target.write("\n")
target.write(the_other_thing)
target.write("\n")

print "And finally, we close it."
target.close()
