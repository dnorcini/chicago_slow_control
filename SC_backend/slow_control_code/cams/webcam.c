/*
 * (c) 1998-2000 Gerd Knorr
 * (c) 2000-2004 Tom Gilbert
 * (c) 2008      James Nikkel
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <math.h>
#include <stdarg.h>
#include <errno.h>
#include <fcntl.h>
#include <time.h>
#include <errno.h>
#include <signal.h>
#include <sys/time.h>
#include <sys/mman.h>
#include <sys/ioctl.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <sys/wait.h>
#include <sys/select.h>
#include <signal.h>
#include <X11/Xlib.h>
#include <Imlib2.h>
#include <giblib.h>

#include <sys/types.h>
#include <linux/videodev.h>

#define max_num_cams 64

#define PIDFILE "/var/run/webcam.pid"
#define CONFIGFILE "/etc/webcam.conf"
#define LOCKFILE "/var/lock/"

#define COMMENT_CHARS "//"

pid_t childpid = 0;

char *grab_text = "";           /* strftime */
char *logfile = NULL;
int grab_width = 640;
int grab_height = 480;
int grab_quality = 90;
int text_r = 255;
int text_g = 255;
int text_b = 255;
int text_a = 255;
int title_r = 255;
int title_g = 255;
int title_b = 255;
int title_a = 255;
int bg_r = 0;
int bg_g = 0;
int bg_b = 0;
int bg_a = 150;
int cam_contrast = 50;
int cam_brightness = 50;
int cam_hue = 50;
int cam_colour = 50;
int cam_whiteness = 50;
int cam_framerate = 10;
char title_font[128];
char text_font[128];
char ttf_dir[256];

char *title_text = NULL;
gib_style *title_style = NULL;
gib_style *text_style = NULL;
char *title_style_file = NULL;
char *text_style_file = NULL;
Imlib_Font title_fn, text_fn;
int device_palette;


int crop = 0;
int crop_x = 0;
int crop_y = 0;
int crop_width = 0;
int crop_height = 0;

int scale = 0;
int scale_width = 0;
int scale_height = 0;

int v_width[5] = { 128, 160, 176, 320, 640 };
int v_height[5] = { 96, 120, 144, 240, 480 };
int v_curr = -1;
int v_force = 0;
int delay_correct = 1;
int flip_horizontal = 0;
int flip_vertical = 0;
int orientation = 0;

struct video_picture cam_pic;

/* these work for v4l only, not v4l2 */
int grab_input = 0;
int grab_norm = VIDEO_MODE_PAL;

static struct video_mmap grab_buf;
static int grab_size = 0;
static unsigned char *grab_data = NULL;

void camlog(char *fmt, ...);

Imlib_Image convert_yuv420p_to_imlib2(unsigned char *mem,
                                      int width,
                                      int height);

int save_image(Imlib_Image image,
               char *file);


struct timeval add_timevals( struct timeval t1, struct timeval t2 )
{
  struct timeval tr;
  tr.tv_usec = t1.tv_usec + t2.tv_usec;
  tr.tv_sec = t1.tv_sec + t2.tv_sec + tr.tv_usec/1000000;
  tr.tv_usec = tr.tv_usec%1000000;
  return tr;
}

struct timeval sub_timevals( struct timeval t1, struct timeval t2 )
{
  struct timeval tr;
  tr.tv_usec = t1.tv_usec - t2.tv_usec;
  tr.tv_sec = t1.tv_sec - t2.tv_sec + t1.tv_usec/1000000;
  while( tr.tv_usec<0 ){
    tr.tv_sec-=1;
    tr.tv_usec+=1000000;
  }
  return tr;
}

float float_timeval( struct timeval t )
{
  float f = 1e-6 * t.tv_usec;
  f += t.tv_sec;
  return f;
}

struct timeval timeval_float( float f )
{
  struct timeval tr;
  tr.tv_sec = (time_t) floor(f);
  f -= tr.tv_sec;
  tr.tv_usec = (suseconds_t) round(1e6*f);
  return tr;
}

void close_device(int grab_fd)
{
  if (munmap(grab_data, grab_size) != 0) {
    perror("munmap()");
    exit(1);
  }
  grab_data = NULL;
  if (close(grab_fd))
    perror("close(grab_fd) ");
}

int try_palette(int fd,
            int pal,
            int depth)
{
  cam_pic.palette = pal;
  cam_pic.depth = depth;
  if (ioctl(fd, VIDIOCSPICT, &cam_pic) < 0)
    return 0;
  if (ioctl(fd, VIDIOCGPICT, &cam_pic) < 0)
    return 0;
  if (cam_pic.palette == pal)
    return 1;
  return 0;
}

int find_palette(int fd,
		 struct video_mmap *vid)
{
    if (try_palette(fd, VIDEO_PALETTE_RGB24, 24)) {
	camlog("negotiated palette RGB24\n");
	return VIDEO_PALETTE_RGB24;
    }
    if (try_palette(fd, VIDEO_PALETTE_YUV420P, 16)) {
	camlog("negotiated palette YUV420P\n");
	return VIDEO_PALETTE_YUV420P;
    }
    if (try_palette(fd, VIDEO_PALETTE_YUV420, 16)) {
	camlog("negotiated palette YUV420\n");
	return VIDEO_PALETTE_YUV420;
    }
    fprintf(stderr,
	    "No supported palette found, please report your device to the author\n");
    exit(2);
    return 0;
}

int grab_init(char *grab_device)
{
    struct video_capability grab_cap;
    struct video_channel grab_chan;
    struct video_mbuf vid_mbuf;
    int grab_fd;
    
    if ((grab_fd = open(grab_device, O_RDWR)) == -1) 
    {
	fprintf(stderr, "open %s: %s\n", grab_device, strerror(errno));
	return(-1);
    }
    if (ioctl(grab_fd, VIDIOCGCAP, &grab_cap) == -1) 
    {
	fprintf(stderr, "%s: no v4l device\n", grab_device);
	return(-1);
    }
    
    if (ioctl(grab_fd, VIDIOCGPICT, &cam_pic) < 0)
	perror("getting pic info");
    cam_pic.contrast = 65535 * ((float) cam_contrast / 100);
    cam_pic.brightness = 65535 * ((float) cam_brightness / 100);
    cam_pic.hue = 65535 * ((float) cam_hue / 100);
    cam_pic.colour = 65535 * ((float) cam_colour / 100);
    cam_pic.whiteness = 65535 * ((float) cam_whiteness / 100);
    if (ioctl(grab_fd, VIDIOCSPICT, &cam_pic) < 0)
	perror("setting cam pic");
    device_palette = find_palette(grab_fd, &grab_buf);
    
    grab_buf.format = device_palette;
    grab_buf.frame = 0;
    grab_buf.width = grab_width;
    grab_buf.height = grab_height;
    
    ioctl(grab_fd, VIDIOCGMBUF, &vid_mbuf);
    camlog("%s detected\n", grab_cap.name);
    
    /* set image source and TV norm */
    grab_chan.channel = grab_input;
    if (ioctl(grab_fd, VIDIOCGCHAN, &grab_chan) == -1) {
	perror("ioctl VIDIOCGCHAN");
	return(-1);
    }
    grab_chan.channel = grab_input;
    grab_chan.norm = grab_norm;
    if (ioctl(grab_fd, VIDIOCSCHAN, &grab_chan) == -1) {
	perror("ioctl VIDIOCSCHAN");
	return(-1);
    }
    
    /*   grab_size = grab_buf.width * grab_buf.height * 3; */
    grab_size = vid_mbuf.size;
    grab_data =
	mmap(0, grab_size, PROT_READ | PROT_WRITE, MAP_SHARED, grab_fd, 0);
    if ((grab_data == NULL) || (MAP_FAILED == grab_data)) 
    {
	fprintf(stderr,
		"couldn't mmap vidcam. your card doesn't support that?\n");
	return(-1);
    }
    return(grab_fd);
}

/* This is a really simplistic approach. Speedups are welcomed. */
Imlib_Image
convert_yuv420p_to_imlib2(unsigned char *src,
                          int width,
                          int height)
{
  int line, col;
  int y, u, v, yy, vr = 0, ug = 0, vg = 0, ub = 0;
  int r, g, b;
  unsigned char *sy, *su, *sv;
  Imlib_Image im;
  DATA32 *data, *dest;

  im = imlib_create_image(width, height);
  imlib_context_set_image(im);
  data = imlib_image_get_data();
  dest = data;

  sy = src;
  su = sy + (width * height);
  sv = su + (width * height / 4);

  for (line = 0; line < height; line++) {
    for (col = 0; col < width; col++) {
      y = *sy++;
      yy = y << 8;
      u = *su - 128;
      ug = 88 * u;
      ub = 454 * u;
      v = *sv - 128;
      vg = 183 * v;
      vr = 359 * v;

      if ((col & 1) == 0) {
        su++;
        sv++;
      }

      r = (yy + vr) >> 8;
      g = (yy - ug - vg) >> 8;
      b = (yy + ub) >> 8;

      if (r < 0)
        r = 0;
      if (r > 255)
        r = 255;
      if (g < 0)
        g = 0;
      if (g > 255)
        g = 255;
      if (b < 0)
        b = 0;
      if (b > 255)
        b = 255;

      *dest = (r << 16) | (g << 8) | b | 0xff000000;
      dest++;
    }
    if ((line & 1) == 0) {
      su -= width / 2;
      sv -= width / 2;
    }
  }
  imlib_image_put_back_data(data);
  return im;
}

/* This is a really simplistic approach. Speedups are welcomed. */
Imlib_Image
convert_yuv420i_to_imlib2(unsigned char *src,
                          int width,
                          int height)
{
  int line, col, linewidth;
  int y, u, v, yy, vr = 0, ug = 0, vg = 0, ub = 0;
  int r, g, b;
  unsigned char *sy, *su, *sv;
  Imlib_Image im;
  DATA32 *data, *dest;

  im = imlib_create_image(width, height);
  imlib_context_set_image(im);
  data = imlib_image_get_data();
  dest = data;

  linewidth = width + (width >> 1);
  sy = src;
  su = sy + 4;
  sv = su + linewidth;

  /* 
     The biggest problem is the interlaced data, and the fact that odd
     add even lines have V and U data, resp. 
   */

  for (line = 0; line < height; line++) {
    for (col = 0; col < width; col++) {
      y = *sy++;
      yy = y << 8;
      if ((col & 1) == 0) {
        /* only at even colums we update the u/v data */
        u = *su - 128;
        ug = 88 * u;
        ub = 454 * u;
        v = *sv - 128;
        vg = 183 * v;
        vr = 359 * v;

        su++;
        sv++;
      }
      if ((col & 3) == 3) {
        sy += 2;        /* skip u/v */
        su += 4;        /* skip y */
        sv += 4;        /* skip y */
      }
      r = (yy + vr) >> 8;
      g = (yy - ug - vg) >> 8;
      b = (yy + ub) >> 8;

      if (r < 0)
        r = 0;
      if (r > 255)
        r = 255;
      if (g < 0)
        g = 0;
      if (g > 255)
        g = 255;
      if (b < 0)
        b = 0;
      if (b > 255)
        b = 255;

      *dest = (r << 16) | (g << 8) | b | 0xff000000;
      dest++;
    }
    if (line & 1) {
      su += linewidth;
      sv += linewidth;
    } else {
      su -= linewidth;
      sv -= linewidth;
    }
  }
  imlib_image_put_back_data(data);
  return im;
}


Imlib_Image 
convert_rgb24_to_imlib2(unsigned char *mem,
                        int width,
                        int height)
{
  Imlib_Image im;
  DATA32 *data, *dest;
  unsigned char *src;
  int i;

  im = imlib_create_image(width, height);
  imlib_context_set_image(im);
  data = imlib_image_get_data();

  dest = data;
  src = mem;
  i = width * height;
  while (i--) {
    *dest = (src[2] << 16) | (src[1] << 8) | src[0] | 0xff000000;
    dest++;
    src += 3;
  }

  imlib_image_put_back_data(data);

  return im;
}


Imlib_Image 
grab_one(int *width,
         int *height, char *grab_device, int pre_samples)
{
  Imlib_Image im;
  int i = 0;
  int grab_fd;

  grab_fd = grab_init(grab_device);
  if (grab_fd == -1)
      return NULL;

  if (pre_samples == 0)
    pre_samples++;

  while (pre_samples--)
  {
      if (ioctl(grab_fd, VIDIOCMCAPTURE, &grab_buf) == -1) 
      {
	  perror("ioctl VIDIOCMCAPTURE");
	  return NULL;
      }
      if (ioctl(grab_fd, VIDIOCSYNC, &i) == -1) 
      {
	  perror("ioctl VIDIOCSYNC");
	  return NULL;
      }
  }
  switch (device_palette) 
  {
      case VIDEO_PALETTE_YUV420P:
	  im =
	      convert_yuv420p_to_imlib2(grab_data, grab_buf.width, grab_buf.height);
	  break;
      case VIDEO_PALETTE_YUV420:
	  im =
	      convert_yuv420i_to_imlib2(grab_data, grab_buf.width, grab_buf.height);
	  break;
      case VIDEO_PALETTE_RGB24:
	  im =
	      convert_rgb24_to_imlib2(grab_data, grab_buf.width, grab_buf.height);
	  break;
      default:
	  fprintf(stderr, "eeek");
	  exit(2);
  }
  close_device(grab_fd);
  if (im) 
  {
      imlib_context_set_image(im);
      imlib_image_attach_data_value("quality", NULL, grab_quality, NULL);
  }
  *width = grab_buf.width;
  *height = grab_buf.height;
  return im;
}

char *get_message(char *message_file)
{
  static char buffer[4096];
  FILE *fp;

  fp = fopen(message_file, "r");
  if (fp) {
    fgets(buffer, sizeof(buffer), fp);
    fclose(fp);
    return buffer;
  }
  return NULL;
}


void add_image_text(Imlib_Image image,
              char *message,
              int width,
              int height)
{
    time_t t;
    struct tm *tm;
    char line[255], title_line[255];
    int len;
    int x, y, w, h;
    
    time(&t);
    tm = localtime(&t);
    strftime(line, 254, grab_text, tm);
    if (title_text)
	strftime(title_line, 254, title_text, tm);
    
    if (message)
	strcat(line, message);
    line[127] = '\0';
    
    len = strlen(line);
    
    if (line[len - 1] == '\n')
	line[--len] = '\0';
    
    if (title_text && title_fn) 
    {
	gib_imlib_get_text_size(title_fn, title_line, title_style, &w, &h,
				IMLIB_TEXT_TO_RIGHT);
	x = width - w - 2;
	y = 2;
	gib_imlib_image_fill_rectangle(image, x - 2, y - 1, w + 4, h + 2, bg_r,
				       bg_g, bg_b, bg_a);
	gib_imlib_text_draw(image, title_fn, title_style, x, y, title_line,
			    IMLIB_TEXT_TO_RIGHT, title_r, title_g, title_b,
			    title_a);
    }
    
    if (text_fn) 
    {
	gib_imlib_get_text_size(text_fn, line, text_style, &w, &h,
				IMLIB_TEXT_TO_RIGHT);
	x = 2;
	y = height - h - 2;
	gib_imlib_image_fill_rectangle(image, x - 2, y - 1, w + 4, h + 2, bg_r,
				       bg_g, bg_b, bg_a);
	gib_imlib_text_draw(image, text_fn, text_style, x, y, line,
			    IMLIB_TEXT_TO_RIGHT, text_r, text_g, text_b, text_a);
    }
}


void camlog(char *fmt,   ...)
{
  va_list args;
  time_t t;
  struct tm *tm;
  char date[128];
  FILE *fp;

  if (!logfile) {
    va_start(args, fmt);
    fprintf(stderr, "webcam: ");
    vfprintf(stderr, fmt, args);
    va_end(args);
    return;
  }

  fp = fopen(logfile, "a");
  if (!fp) {
    fprintf(stderr, "can't open log file %s\n", logfile);
    exit(2);
  }

  time(&t);
  tm = localtime(&t);
  strftime(date, 127, "%d/%m %H:%M:%S", tm);
  fprintf(fp, "%s  ", date);
  va_start(args, fmt);
  vfprintf(fp, fmt, args);
  va_end(args);
  fclose(fp);
}

int save_image(Imlib_Image image,
           char *file)
{
    Imlib_Load_Error err;
    
    gib_imlib_save_image_with_error_return(image, file, &err);
    if ((err) || (!image)) 
    {
	switch (err) 
	{
	    case IMLIB_LOAD_ERROR_FILE_DOES_NOT_EXIST:
		camlog("Error saving image %s - File does not exist", file);
		break;
	    case IMLIB_LOAD_ERROR_FILE_IS_DIRECTORY:
		camlog("Error saving image %s - Directory specified for image filename",
		       file);
		break;
	    case IMLIB_LOAD_ERROR_PERMISSION_DENIED_TO_READ:
		camlog("Error saving image %s - No read access to directory", file);
		break;
	    case IMLIB_LOAD_ERROR_UNKNOWN:
	    case IMLIB_LOAD_ERROR_NO_LOADER_FOR_FILE_FORMAT:
		camlog("Error saving image %s - No Imlib2 loader for that file format",
		       file);
		break;
	    case IMLIB_LOAD_ERROR_PATH_TOO_LONG:
		camlog("Error saving image %s - Path specified is too long", file);
		break;
	    case IMLIB_LOAD_ERROR_PATH_COMPONENT_NON_EXISTANT:
		camlog("Error saving image %s - Path component does not exist", file);
		break;
	    case IMLIB_LOAD_ERROR_PATH_COMPONENT_NOT_DIRECTORY:
		camlog("Error saving image %s - Path component is not a directory",
		       file);
		break;
	    case IMLIB_LOAD_ERROR_PATH_POINTS_OUTSIDE_ADDRESS_SPACE:
		camlog("Error saving image %s - Path points outside address space",
		       file);
		break;
	    case IMLIB_LOAD_ERROR_TOO_MANY_SYMBOLIC_LINKS:
		camlog("Error saving image %s - Too many levels of symbolic links",
		       file);
		break;
	    case IMLIB_LOAD_ERROR_OUT_OF_MEMORY:
		camlog("Error saving image %s - Out of memory", file);
		break;
	    case IMLIB_LOAD_ERROR_OUT_OF_FILE_DESCRIPTORS:
		gib_eprintf("While loading %s - Out of file descriptors", file);
		break;
	    case IMLIB_LOAD_ERROR_PERMISSION_DENIED_TO_WRITE:
		camlog("Error saving image %s - Cannot write to directory", file);
		break;
	    case IMLIB_LOAD_ERROR_OUT_OF_DISK_SPACE:
		camlog("Error saving image %s - Cannot write - out of disk space", file);
		break;
	    default:
		camlog
		    ("Error saving image %s - Unknown error (%d). Attempting to continue",
		     file, err);
		break;
	}
	return 0;
    }
    return 1;
}
int my_signal = 0;

void handler(int sent_signal)
{
    my_signal = sent_signal;
}




int main(int argc, char *argv[])
{
    char       **my_argv;
    Imlib_Image image, tmp_image;
    int width, height;
    struct timeval start_shot;
    struct timeval end_shot;
    struct timeval new_delay;
    int dev_indx;
    char grab_device[128];
    char grab_device_root[128];
    char pic_file[256];
    char pic_file_root[256];
    int  pre_samples = 1;
    float grab_delay = 1;

    int        i;

    // save restart arguments
    my_argv = malloc(sizeof(char *) * (argc + 1));
    for (i = 0; i < argc; i++) 
    {
	my_argv[i] = strdup(argv[i]);
    }
    my_argv[i] = NULL;   
            
    sprintf(grab_device_root, "/dev/video");
    sprintf(pic_file_root, "/var/www/html/php/webcam/cam");
    
    sprintf(ttf_dir, "/usr/share/fonts/bitstream-vera/");

    sprintf(title_font, "VeraBd/12");
    sprintf(text_font, "VeraBd/12");

    pre_samples = 40;
    grab_delay = 5.23;

    // print config 
    camlog("webcam 1.99 - (c) 1999, 2008 Gerd Knorr, Tom Gilbert, James Nikkel\n");
    camlog("grabber config: size %dx%d, input %d, norm %d, " "jpeg quality %d\n",
	   grab_width, grab_height, grab_input, grab_norm, grab_quality);
    
    imlib_context_set_direction(IMLIB_TEXT_TO_RIGHT);
    imlib_add_path_to_font_path(ttf_dir);
    imlib_add_path_to_font_path(".");
    imlib_context_set_operation(IMLIB_OP_COPY);
    imlib_set_cache_size(0);
    if (title_style_file)
	title_style = gib_style_new_from_ascii(title_style_file);
    if (text_style_file)
	text_style = gib_style_new_from_ascii(text_style_file);
    title_fn = imlib_load_font(title_font);
    if (!title_fn)
	fprintf(stderr, "can't load font %s\n", title_font);
    text_fn = imlib_load_font(text_font);
    if (!text_fn)
	fprintf(stderr, "can't load font %s\n", text_font);


    
  // detach current process
  //daemonize();
  
  // ignore these signals 
  signal(SIGINT, SIG_IGN);
  signal(SIGQUIT, SIG_IGN);
 
  // install signal handler
  signal(SIGHUP, handler);
  signal(SIGINT, handler);
  signal(SIGQUIT, handler);
  signal(SIGTERM, handler);


  // main loop 
  do {
    end_shot.tv_sec = 0;
    end_shot.tv_usec = 0;
    start_shot.tv_sec = 0;
    start_shot.tv_usec = 0;

    
    gettimeofday(&start_shot, 0);
    
    dev_indx = 0;  
    do
    {
	sprintf(grab_device, "%s%d", grab_device_root, dev_indx);
	
	camlog("* taking shot\n");
	
	image = grab_one(&width, &height, grab_device, pre_samples);
	
	imlib_context_set_image(image);
	if (!image) {
	    break;
	}
	
	if (crop) 
	{
	    if (!crop_width)
		crop_width = grab_width;
	    if (!crop_height)
		crop_height = grab_height;
	    tmp_image =
		gib_imlib_create_cropped_scaled_image(image, crop_x, crop_y,
						      crop_width, crop_height,
						      crop_width, crop_height, 1);
	    gib_imlib_free_image_and_decache(image);
	    image = tmp_image;
	    imlib_context_set_image(image);
	    
	    /* Set new values for width and height, else the image's 
	       text might not show up in the correct position. */
	    width = crop_width;
	    height = crop_height;
	}
	
	if (scale) 
	{
	    if (!scale_width)
		scale_width = grab_width;
	    if (!scale_height)
		scale_height = grab_height;
	    
	    tmp_image =
		gib_imlib_create_cropped_scaled_image(image, 0, 0, scale_width,
						      scale_height, scale_width,
						      scale_height, 1);
	    gib_imlib_blend_image_onto_image(tmp_image, image, 1, 0, 0,
					     grab_width, grab_height, 0, 0,
					     scale_width, scale_height, 1, 0, 0);
	    gib_imlib_free_image_and_decache(image);
	    image = tmp_image;
	    imlib_context_set_image(image);
	    
	    /* Set new values for width and height, else the image's 
	       text might not show up in the correct position. */
	    width = scale_width;
	    height = scale_height;
	}
	
	camlog("** shot taken\n");
	
	if (flip_horizontal) {
	    imlib_image_flip_horizontal();
	}
   
	if (flip_vertical) {
	    imlib_image_flip_vertical();
	}
	
	if (orientation && orientation > 0 && orientation < 4) 
	{
	    imlib_image_orientate(orientation);
	    /* Changing orientation flips width and height, so we must swap them. */
	    int swap_dimensions;
	    if(orientation == 1 || orientation == 3) {
		swap_dimensions = height;
		height = width;
		width = swap_dimensions;
	    }
	}
	
	system("date > /tmp/info_file");
	add_image_text(image, get_message("/tmp/info_file"), width, height);
	
	sprintf(pic_file, "%s%d.jpg", pic_file_root, dev_indx);
	save_image(image, pic_file);
	
	gib_imlib_free_image_and_decache(image);
	gettimeofday(&end_shot,0);
	dev_indx++;
    }
    while(dev_indx < max_num_cams);
    
    
    
    new_delay = timeval_float(grab_delay);
    
    end_shot = sub_timevals(end_shot, start_shot);
    
    if (delay_correct) 
    {
	new_delay = sub_timevals(new_delay, end_shot);
	if (float_timeval(new_delay) < 0) 
	{
	    new_delay.tv_sec = 0;
	    new_delay.tv_usec = 0;
	}
	camlog("Sleeping %f secs (corrected)\n", new_delay);
    } 
    else {
	camlog("Sleeping %f secs\n", grab_delay);
    }
    
    if (float_timeval(new_delay) > 0)
	select( 0, 0, 0, 0, &new_delay );
    
    
  } while(my_signal == 0);
  
  
  if (my_signal == SIGHUP)         ///  restart called
  {
      long fd;
      // close all files before restart
      for (fd = sysconf(_SC_OPEN_MAX); fd > 2; fd--) 
      {
	  int flag;
	  if ((flag = fcntl(fd, F_GETFD, 0)) != -1)
	      fcntl(fd, F_SETFD, flag | FD_CLOEXEC);
      }
      
      sleep(2);
      execv(my_argv[0], my_argv);
      fprintf(stderr, "execv() failed: %s", strerror(errno));
      exit(1);
  }
  
  for (i = 0; my_argv[i] != NULL; i++)
  {
      free(my_argv[i]);
  }
  free(my_argv);
  
  exit(0);
}

