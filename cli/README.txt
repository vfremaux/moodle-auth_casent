How to use the fixture launcher. 
######################################

the fixture_launcher is a tool that spread workers to process a 
data and configuration fixing script called as "fixture".

The launcher get all known hosts in the block_vmoodle table and process
any active virtual moodle registered in it. It will NOT process the master moodle.
Workers are launched in parallel to use several processors and threads and accelerate
the processing. 4 workers are lauched as a default.

You just need 'cd' to this directory with a command line terminal, and 
run : 

php fixture_launcher --fixture=<fixturename>

To launch the CAS ent quickconfig fixture :

php fixture_launcher.php --logroot=/data/log/moodle --fixture=quickconfig --distributed

The logroot parameter let you divert the output logging to a directory of our choice.

Other options:

--workers: You can change the number of workers using this parameter. Ex: --workers=2
--distributed: If not mentionned, the processing will be scheduled in serial batch, not parallelising workers.
--include: Adds a LIKE pattern the the target host selection
--exclude: Adds a NOT LIKE pattern to the target host selection